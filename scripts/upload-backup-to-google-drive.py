#!/usr/bin/env python3
"""Upload one encrypted PostgreSQL dump to a specific Google Drive folder."""

from __future__ import annotations

import json
import os
import sys
from pathlib import Path

from google.oauth2.credentials import Credentials
from google.oauth2 import service_account
from googleapiclient.discovery import build
from googleapiclient.http import MediaFileUpload

# This scope grants access only to files created by this backup application,
# rather than every file in the Google Drive account.
DRIVE_SCOPE = "https://www.googleapis.com/auth/drive.file"


def required_env(name: str) -> str:
    value = os.environ.get(name)
    if not value:
        raise RuntimeError(f"Missing required environment variable: {name}")
    return value


def resolve_credentials() -> Credentials:
    service_account_file = os.environ.get("GOOGLE_SERVICE_ACCOUNT_JSON_FILE")
    if service_account_file:
        return service_account.Credentials.from_service_account_file(
            service_account_file,
            scopes=[DRIVE_SCOPE],
        )

    return Credentials(
        token=None,
        refresh_token=required_env("GOOGLE_OAUTH_REFRESH_TOKEN"),
        token_uri="https://oauth2.googleapis.com/token",
        client_id=required_env("GOOGLE_OAUTH_CLIENT_ID"),
        client_secret=required_env("GOOGLE_OAUTH_CLIENT_SECRET"),
        scopes=[DRIVE_SCOPE],
    )


def upload(backup_file: Path) -> dict:
    folder_id = required_env("GOOGLE_DRIVE_FOLDER_ID")

    if not backup_file.is_file() or backup_file.stat().st_size == 0:
        raise RuntimeError(f"Backup file is missing or empty: {backup_file}")

    credentials = resolve_credentials()
    drive = build("drive", "v3", credentials=credentials, cache_discovery=False)

    metadata = {
        "name": backup_file.name,
        "parents": [folder_id],
        "description": "Encrypted Warehouse database backup created by GitHub Actions.",
    }
    media = MediaFileUpload(
        str(backup_file),
        mimetype="application/octet-stream",
        resumable=True,
        chunksize=10 * 1024 * 1024,
    )
    request = drive.files().create(
        body=metadata,
        media_body=media,
        fields="id,name,size,createdTime,md5Checksum",
        supportsAllDrives=True,
    )

    response = None
    while response is None:
        status, response = request.next_chunk()
        if status:
            print(f"Upload progress: {int(status.progress() * 100)}%")

    return response


def main() -> int:
    if len(sys.argv) != 2:
        print("Usage: upload-backup-to-google-drive.py BACKUP_FILE", file=sys.stderr)
        return 2

    uploaded = upload(Path(sys.argv[1]))
    print(json.dumps(uploaded, sort_keys=True))
    return 0


if __name__ == "__main__":
    try:
        raise SystemExit(main())
    except Exception as error:
        print(f"Google Drive upload failed: {error}", file=sys.stderr)
        raise SystemExit(1)
