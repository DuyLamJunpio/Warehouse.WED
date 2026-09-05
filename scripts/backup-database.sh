#!/usr/bin/env bash

set -Eeuo pipefail

: "${DB_HOST:?Missing BACKUP_DB_HOST secret}"
: "${DB_PORT:?Missing BACKUP_DB_PORT secret}"
: "${DB_DATABASE:?Missing BACKUP_DB_DATABASE secret}"
: "${DB_USERNAME:?Missing BACKUP_DB_USERNAME secret}"
: "${DB_PASSWORD:?Missing BACKUP_DB_PASSWORD secret}"

backup_dir="${1:?Pass the output directory as the first argument}"
timestamp="$(date -u +%Y%m%dT%H%M%SZ)"
backup_file="${backup_dir}/warehouse-${timestamp}.dump"

mkdir -p "$backup_dir"

# Supabase requires TLS. The username must remain postgres.<project-ref> when
# using the IPv4 Session Pooler.
export PGPASSWORD="$DB_PASSWORD"
export PGSSLMODE="require"
trap 'unset PGPASSWORD' EXIT

pg_dump \
  --host "$DB_HOST" \
  --port "$DB_PORT" \
  --username "$DB_USERNAME" \
  --dbname "$DB_DATABASE" \
  --format=custom \
  --no-owner \
  --no-privileges \
  --file "$backup_file"

test -s "$backup_file"
printf '%s\n' "$backup_file"
