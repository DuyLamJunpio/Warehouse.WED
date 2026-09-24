<?php

namespace App\Services;

use App\Models\ImageModel;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Lưu media theo nội dung, không theo tên file người dùng.
 *
 * Cùng một binary có cùng SHA-256 và cùng đường dẫn trên bucket. Trong một sản
 * phẩm nó còn dùng lại chính bản ghi ImageModel, nên hai mẫu dùng chung ảnh chỉ
 * chiếm một object storage và một dòng media.
 */
class ProductMediaService
{
    public function store(Product $product, UploadedFile $file, array $attributes = []): ImageModel
    {
        $realPath = $file->getRealPath();
        $sha256 = $realPath ? hash_file('sha256', $realPath) : false;

        if ($sha256) {
            $existing = ImageModel::where('product_id', $product->id)
                ->where('sha256', $sha256)
                ->first();

            if ($existing) {
                // Trường hợp cùng file được chọn làm ảnh ghim ở lần gửi sau:
                // vẫn dùng lại bản ghi cũ nhưng không làm mất ý định ghim ảnh.
                if (($attributes['is_pined'] ?? false) && ! $existing->is_pined) {
                    $existing->is_pined = true;
                    $existing->save();
                }

                return $existing;
            }
        }

        $isVideo = str_starts_with((string) $file->getMimeType(), 'video/');
        $extension = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'bin');
        // hashName() đã chứa phần mở rộng. Chỉ lấy tên gốc để không sinh
        // đường dẫn kiểu "abc.png.png" trong trường hợp hiếm không đọc được hash.
        $baseName = $sha256 ?: pathinfo($file->hashName(), PATHINFO_FILENAME);
        $path = 'public/images/' . $baseName . '.' . $extension;

        // Hai sản phẩm có thể dùng cùng binary. Nếu object đã có trên bucket thì
        // chỉ tạo liên kết DB mới; tuyệt đối không tải cùng bytes lên lần nữa.
        if (!Storage::exists($path)) {
            $storedPath = $file->storeAs('public/images', $baseName . '.' . $extension);
            if ($storedPath === false) {
                throw new \RuntimeException('Không lưu được tệp sản phẩm vào storage.');
            }
        }

        $values = [
            'path' => $path,
            'byte_size' => $file->getSize() ?: null,
            'name' => $file->getClientOriginalName(),
            'media_type' => $isVideo ? ImageModel::TYPE_VIDEO : ImageModel::TYPE_IMAGE,
            'sort_order' => (int) ($attributes['sort_order'] ?? 0),
            'is_pined' => (bool) ($attributes['is_pined'] ?? false),
        ];

        if ($sha256) {
            // firstOrCreate xử lý cả trường hợp hai request cùng tải đúng một
            // binary cho cùng sản phẩm: unique(product_id, sha256) vẫn chỉ để
            // lại một dòng ImageModel.
            return ImageModel::firstOrCreate([
                'product_id' => $product->id,
                'sha256' => $sha256,
            ], $values);
        }

        return ImageModel::create([
            'product_id' => $product->id,
            'sha256' => null,
            ...$values,
        ]);
    }

    /**
     * Xóa media chỉ khi không có mẫu nào dùng. Object chung trên bucket chỉ bị
     * gỡ sau khi bản ghi cuối cùng tham chiếu đường dẫn đó đã biến mất.
     */
    public function delete(ImageModel $media): bool
    {
        if ($media->styles()->exists()) {
            return false;
        }

        $path = $media->path;
        $media->delete();

        if (!ImageModel::where('path', $path)->exists()) {
            Storage::delete($path);
        }

        return true;
    }
}
