<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pokemon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScreenshotRecognitionController extends Controller
{
    /**
     * POST /api/v1/recognize-pokemon
     * スクリーンショット画像からポケモンを認識する
     */
    public function recognize(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,gif,webp|max:10240',
        ]);

        if (!extension_loaded('gd')) {
            return response()->json(['error' => 'GD拡張が有効になっていません'], 500);
        }

        $uploadedFile = $request->file('image');
        $uploadedImg = $this->loadImageFromPath($uploadedFile->getPathname(), $uploadedFile->getMimeType());
        if (!$uploadedImg) {
            return response()->json(['error' => '画像の読み込みに失敗しました'], 422);
        }
        $uploadedHist = $this->getHistogram($uploadedImg);
        imagedestroy($uploadedImg);

        // ローカル保存スプライトを持つポケモンのみ対象
        $pokemonList = Pokemon::with('types')
            ->whereNotNull('sprite_url')
            ->where('sprite_url', 'like', '/storage/%')
            ->get();

        if ($pokemonList->isEmpty()) {
            return response()->json([
                'results' => [],
                'message' => 'ローカル保存済みスプライトがありません。マスター管理画面でポケモン画像をアップロードしてください。',
            ]);
        }

        $results = [];
        foreach ($pokemonList as $pokemon) {
            $spritePath = storage_path('app/public/' . str_replace('/storage/', '', $pokemon->sprite_url));
            if (!file_exists($spritePath)) {
                continue;
            }

            $spriteImg = $this->loadImageFromPath($spritePath);
            if (!$spriteImg) {
                continue;
            }

            $spriteHist = $this->getHistogram($spriteImg);
            imagedestroy($spriteImg);

            $similarity = $this->compareHistograms($uploadedHist, $spriteHist);
            $results[] = [
                'pokemon'    => $pokemon,
                'similarity' => round($similarity * 100, 1),
            ];
        }

        usort($results, fn($a, $b) => $b['similarity'] <=> $a['similarity']);

        return response()->json([
            'results' => array_slice($results, 0, 8),
        ]);
    }

    private function loadImageFromPath(string $path, ?string $mime = null): ?\GdImage
    {
        $mime = $mime ?? (mime_content_type($path) ?: '');
        return match (true) {
            str_contains($mime, 'jpeg') || str_contains($mime, 'jpg') => @imagecreatefromjpeg($path),
            str_contains($mime, 'png')  => @imagecreatefrompng($path),
            str_contains($mime, 'gif')  => @imagecreatefromgif($path),
            str_contains($mime, 'webp') => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : null,
            default                     => null,
        };
    }

    /**
     * 画像を32×32にリサイズして色ヒストグラムを取得（R/G/B各16バケット）
     */
    private function getHistogram(\GdImage $img): array
    {
        $resized = imagecreatetruecolor(32, 32);
        // 透過対応: 白背景を設定してからコピー
        $white = imagecolorallocate($resized, 255, 255, 255);
        imagefill($resized, 0, 0, $white);
        imagecopyresampled($resized, $img, 0, 0, 0, 0, 32, 32, imagesx($img), imagesy($img));

        $hist = array_fill(0, 48, 0); // R:0-15, G:16-31, B:32-47
        for ($y = 0; $y < 32; $y++) {
            for ($x = 0; $x < 32; $x++) {
                $color = imagecolorat($resized, $x, $y);
                $r = ($color >> 16) & 0xFF;
                $g = ($color >> 8) & 0xFF;
                $b = $color & 0xFF;
                // 白・ほぼ白ピクセル（背景）はスキップ
                if ($r > 230 && $g > 230 && $b > 230) {
                    continue;
                }
                $hist[(int)($r / 16)]++;
                $hist[16 + (int)($g / 16)]++;
                $hist[32 + (int)($b / 16)]++;
            }
        }
        imagedestroy($resized);
        return $hist;
    }

    /**
     * コサイン類似度で2つのヒストグラムを比較（0〜1）
     */
    private function compareHistograms(array $a, array $b): float
    {
        $dot = 0.0;
        $magA = 0.0;
        $magB = 0.0;
        for ($i = 0; $i < 48; $i++) {
            $dot  += $a[$i] * $b[$i];
            $magA += $a[$i] ** 2;
            $magB += $b[$i] ** 2;
        }
        if ($magA == 0.0 || $magB == 0.0) {
            return 0.0;
        }
        return $dot / (sqrt($magA) * sqrt($magB));
    }
}
