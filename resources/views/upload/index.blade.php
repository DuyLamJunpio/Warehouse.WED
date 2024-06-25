<x-app-layout>
    <div class="mt-5">
        <form action="{{ route('uploads.index') }}" method="post" enctype="multipart/form-data">
            @csrf
            <input type="text" name="ids">
            <input type="file" name="images[]" multiple>
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>
    </div>
</x-app-layout>
