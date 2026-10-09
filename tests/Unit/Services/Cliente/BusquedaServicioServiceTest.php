<?php

namespace Tests\Unit\Services\Cliente;

use App\Services\Cliente\BusquedaServicioService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BusquedaServicioServiceTest extends TestCase
{
    public function test_transcribe_un_audio_con_whisper(): void
    {
        config()->set('services.whisper.url', 'http://127.0.0.1:8095/inference');

        Http::fake([
            'http://127.0.0.1:8095/inference' => Http::response([
                'text' => 'Mi computadora se apaga de repente.',
            ]),
        ]);

        $audio = UploadedFile::fake()->createWithContent('descripcion.webm', 'audio simulado');

        $texto = app(BusquedaServicioService::class)->transcribirAudio([
            'audio' => $audio,
        ]);

        $this->assertSame('Mi computadora se apaga de repente.', $texto);

        Http::assertSent(fn ($request) => $request->url() === 'http://127.0.0.1:8095/inference');
    }

    public function test_rechaza_una_transcripcion_vacia(): void
    {
        config()->set('services.whisper.url', 'http://127.0.0.1:8095/inference');

        Http::fake([
            'http://127.0.0.1:8095/inference' => Http::response(['text' => '']),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Whisper no detecto palabras en la grabacion.');

        app(BusquedaServicioService::class)->transcribirAudio([
            'audio' => UploadedFile::fake()->createWithContent('silencio.webm', 'audio simulado'),
        ]);
    }
}
