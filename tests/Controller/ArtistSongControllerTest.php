<?php

namespace App\Tests\Controller;

use App\Controller\ArtistSongController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use PHPUnit\Framework\TestCase;

class ArtistSongControllerTest extends TestCase
{
    public function testFindMediaUploadsRecognizesFilesWhenMimeIsGeneric(): void
    {
        $coverPath = tempnam(sys_get_temp_dir(), 'cover');
        $audioPath = tempnam(sys_get_temp_dir(), 'audio');

        file_put_contents($coverPath, 'fake image');
        file_put_contents($audioPath, 'fake audio');

        $cover = new UploadedFile($coverPath, 'cover.jpg', 'application/octet-stream', \UPLOAD_ERR_OK, true);
        $audio = new UploadedFile($audioPath, 'track.mp3', 'application/octet-stream', \UPLOAD_ERR_OK, true);

        $controller = new ArtistSongController();
        $method = new \ReflectionMethod($controller, 'findMediaUploads');
        $method->setAccessible(true);

        [$resolvedCover, $resolvedAudio] = $method->invoke($controller, [$cover, $audio]);

        $this->assertSame($cover, $resolvedCover);
        $this->assertSame($audio, $resolvedAudio);
    }
}
