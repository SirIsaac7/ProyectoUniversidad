@extends('layouts.app')
@section('title', 'Configuración de correos')

@push('styles')
<link href="{{ asset('assets/libs/filepond/filepond.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.css') }}" rel="stylesheet">
@endpush

@section('content')
@php
    $puedeEditar = auth()->user()->can('editar configuracion correos');
    $erroresVista = $errors ?? new \Illuminate\Support\ViewErrorBag();
    $tabModuloActivo = $erroresVista->hasAny(['recuperacion_asunto', 'recuperacion_titulo', 'recuperacion_mensaje', 'recuperacion_boton']) ? 'recuperacion' :
        ($erroresVista->hasAny(['backup_destinatarios', 'backup_notificar_exito', 'backup_notificar_error', 'backup_adjuntar_archivo', 'backup_asunto_exito', 'backup_mensaje_exito', 'backup_asunto_error', 'backup_mensaje_error']) ? 'backups' : 'verificacion');
    $destinatarios = old('backup_destinatarios', implode(PHP_EOL, $configuracion->backup_destinatarios ?? []));
    $nombreVista = old('nombre_marca', $configuracion->nombre_marca);
    $colorVista = old('color_primario', $configuracion->color_primario);
    $colorVistaSeguro = preg_match('/^#[0-9A-Fa-f]{6}$/', $colorVista) ? $colorVista : $configuracion->color_primario;
    $logoVista = $configuracion->logo_path ? asset($configuracion->logo_path) : null;
    $valoresEjemplo = ['{nombre}' => 'María', '{aplicacion}' => config('app.name'), '{minutos}' => (string) config('auth.passwords.'.config('auth.defaults.passwords').'.expire'), '{fecha}' => now()->format('d/m/Y H:i'), '{estado}' => 'correcto', '{detalle}' => 'La copia finalizó correctamente.'];
@endphp

<div class="page-title-box bg-transparent border-bottom mb-4 pb-3">
    <h4 class="mb-1 text-uppercase">Configuración de correos</h4>
    <p class="text-muted mb-0">Personaliza la presentación y los mensajes que recibirán los usuarios.</p>
</div>

<form method="POST" action="{{ route('configuracion-correos.update') }}" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div class="row g-4 align-items-start">
        <div class="col-xl-5 col-xxl-4">
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center gap-3 py-3">
                    <span class="avatar-sm"><span class="avatar-title rounded bg-primary-subtle text-primary fs-20"><i class="ri-palette-line"></i></span></span>
                    <div><h5 class="mb-1">Apariencia general</h5><p class="text-muted mb-0">Se aplicará a todos los correos.</p></div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="nombre_marca" class="form-label">Nombre que verá el destinatario</label>
                        <input type="text" id="nombre_marca" name="nombre_marca" class="form-control @error('nombre_marca') is-invalid @enderror" value="{{ $nombreVista }}" required maxlength="100" @disabled(! $puedeEditar)>
                        @error('nombre_marca')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="row g-3 align-items-start">
                        <div class="col-sm-4">
                            <label for="color_primario" class="form-label">Color principal</label>
                            <input type="color" id="color_primario" name="color_primario" class="form-control form-control-color w-100 @error('color_primario') is-invalid @enderror" value="{{ $colorVista }}" required @disabled(! $puedeEditar)>
                            @error('color_primario')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-sm-8">
                            <label for="logo" class="form-label">Logotipo de los correos</label>
                            <input type="file" id="logo" name="logo" class="filepond @error('logo') is-invalid @enderror" accept="image/png,.png" data-max-file-size="5MB" @disabled(! $puedeEditar)>
                            <div class="form-text">PNG de hasta 5 MB.</div>
                            @error('logo')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            @if ($logoVista)
                                <div class="d-flex align-items-center gap-2 border rounded bg-warning-subtle text-warning-emphasis p-2 mt-3">
                                    <i class="ri-alert-line fs-18 flex-shrink-0"></i>
                                    <span class="fs-12">La imagen se reemplazará al guardar.</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between gap-2">
                    <h5 class="mb-0"><i class="ri-eye-line me-1 text-primary"></i>Vista previa</h5>
                    <span id="preview_tipo" class="badge bg-primary-subtle text-primary">Apariencia general</span>
                </div>
                <div class="card-body">
                    <div class="border rounded overflow-hidden mx-auto shadow-sm" style="max-width:520px;">
                        <div id="preview_encabezado" class="p-3 text-center text-white" style="background-color:{{ $colorVistaSeguro }};">
                            <img id="preview_logo" src="{{ $logoVista ?: '' }}" alt="Logotipo" class="{{ $logoVista ? '' : 'd-none' }} mb-2 mx-auto" style="display:block;max-width:140px;max-height:48px;width:auto;height:auto;">
                            <span id="preview_marca" class="fw-semibold text-white">{{ $nombreVista }}</span>
                        </div>
                        <div class="bg-white text-dark p-4 text-center">
                            <i id="preview_icono" class="ri-mail-line fs-36 d-inline-block mb-2" style="color:{{ $colorVistaSeguro }};"></i>
                            <small id="preview_asunto" class="text-muted d-block mb-2"></small>
                            <h5 id="preview_titulo" class="text-dark">Título del mensaje</h5>
                            <p id="preview_mensaje" class="text-muted mb-3" style="white-space:pre-line;">Así se verán los correos enviados por el sistema.</p>
                            <span id="preview_boton" class="btn btn-sm text-white" style="background-color:{{ $colorVistaSeguro }};">Acción del correo</span>
                        </div>
                        <div class="bg-light border-top p-3 text-center text-muted fs-12">Este es un correo automático. Por favor, no respondas a este mensaje.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-7 col-xxl-8">
            <div class="card">
                <div class="card-header py-3">
                    <h5 class="mb-1">Mensajes por módulo</h5><p class="text-muted mb-0">Configura el contenido que se enviará en cada caso.</p>
                </div>
                <div class="card-body p-4">
                    <ul class="nav nav-tabs nav-tabs-custom nav-justified flex-column flex-md-row mb-4" role="tablist">
                        <li class="nav-item"><a class="nav-link {{ $tabModuloActivo === 'verificacion' ? 'active' : '' }}" data-bs-toggle="tab" href="#correo-verificacion" role="tab" data-preview-mode="verificacion"><i class="ri-mail-check-line me-1"></i>Verificación de correo</a></li>
                        <li class="nav-item"><a class="nav-link {{ $tabModuloActivo === 'recuperacion' ? 'active' : '' }}" data-bs-toggle="tab" href="#correo-recuperacion" role="tab" data-preview-mode="recuperacion"><i class="ri-lock-password-line me-1"></i>Recuperación de contraseña</a></li>
                        <li class="nav-item"><a class="nav-link {{ $tabModuloActivo === 'backups' ? 'active' : '' }}" data-bs-toggle="tab" href="#correo-backups" role="tab" data-preview-mode="backup_exito"><i class="ri-database-2-line me-1"></i>Copias de seguridad</a></li>
                    </ul>
                    <div class="tab-content">
                    <div class="tab-pane {{ $tabModuloActivo === 'verificacion' ? 'active show' : '' }}" id="correo-verificacion" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-7"><label for="verificacion_asunto" class="form-label">Asunto</label><input type="text" id="verificacion_asunto" name="verificacion_asunto" class="form-control @error('verificacion_asunto') is-invalid @enderror" value="{{ old('verificacion_asunto', $configuracion->verificacion_asunto) }}" required maxlength="150" @disabled(! $puedeEditar)>@error('verificacion_asunto')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                            <div class="col-md-5"><label for="verificacion_boton" class="form-label">Texto del botón</label><input type="text" id="verificacion_boton" name="verificacion_boton" class="form-control @error('verificacion_boton') is-invalid @enderror" value="{{ old('verificacion_boton', $configuracion->verificacion_boton) }}" required maxlength="60" @disabled(! $puedeEditar)>@error('verificacion_boton')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                            <div class="col-12"><label for="verificacion_titulo" class="form-label">Título</label><input type="text" id="verificacion_titulo" name="verificacion_titulo" class="form-control @error('verificacion_titulo') is-invalid @enderror" value="{{ old('verificacion_titulo', $configuracion->verificacion_titulo) }}" required maxlength="150" @disabled(! $puedeEditar)>@error('verificacion_titulo')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                            <div class="col-12"><label for="verificacion_mensaje" class="form-label">Mensaje</label><textarea id="verificacion_mensaje" name="verificacion_mensaje" rows="7" class="form-control @error('verificacion_mensaje') is-invalid @enderror" required maxlength="1500" @disabled(! $puedeEditar)>{{ old('verificacion_mensaje', $configuracion->verificacion_mensaje) }}</textarea>@error('verificacion_mensaje')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                            <div class="col-12"><div class="alert alert-info mb-0 d-flex gap-3"><i class="ri-information-line fs-22 flex-shrink-0"></i><div><h6 class="alert-heading mb-2">Datos que puedes insertar automáticamente</h6><ul class="mb-2 ps-3"><li><code>{nombre}</code>: nombre de la persona que verificará su cuenta.</li><li><code>{aplicacion}</code>: nombre actual del sistema.</li></ul><p class="mb-0"><strong>Ejemplo:</strong> Hola <code>{nombre}</code>, confirma tu cuenta en <code>{aplicacion}</code>.</p></div></div></div>
                        </div>
                    </div>
                    <div class="tab-pane {{ $tabModuloActivo === 'recuperacion' ? 'active show' : '' }}" id="correo-recuperacion" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-7"><label for="recuperacion_asunto" class="form-label">Asunto</label><input type="text" id="recuperacion_asunto" name="recuperacion_asunto" class="form-control @error('recuperacion_asunto') is-invalid @enderror" value="{{ old('recuperacion_asunto', $configuracion->recuperacion_asunto) }}" required maxlength="150" @disabled(! $puedeEditar)>@error('recuperacion_asunto')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                            <div class="col-md-5"><label for="recuperacion_boton" class="form-label">Texto del botón</label><input type="text" id="recuperacion_boton" name="recuperacion_boton" class="form-control @error('recuperacion_boton') is-invalid @enderror" value="{{ old('recuperacion_boton', $configuracion->recuperacion_boton) }}" required maxlength="60" @disabled(! $puedeEditar)>@error('recuperacion_boton')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                            <div class="col-12"><label for="recuperacion_titulo" class="form-label">Título</label><input type="text" id="recuperacion_titulo" name="recuperacion_titulo" class="form-control @error('recuperacion_titulo') is-invalid @enderror" value="{{ old('recuperacion_titulo', $configuracion->recuperacion_titulo) }}" required maxlength="150" @disabled(! $puedeEditar)>@error('recuperacion_titulo')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                            <div class="col-12"><label for="recuperacion_mensaje" class="form-label">Mensaje</label><textarea id="recuperacion_mensaje" name="recuperacion_mensaje" rows="7" class="form-control @error('recuperacion_mensaje') is-invalid @enderror" required maxlength="1500" @disabled(! $puedeEditar)>{{ old('recuperacion_mensaje', $configuracion->recuperacion_mensaje) }}</textarea>@error('recuperacion_mensaje')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                            <div class="col-12"><div class="alert alert-info mb-0 d-flex gap-3"><i class="ri-information-line fs-22 flex-shrink-0"></i><div><h6 class="alert-heading mb-2">Datos que puedes insertar automáticamente</h6><ul class="mb-2 ps-3"><li><code>{nombre}</code>: nombre de la persona que recuperará su cuenta.</li><li><code>{aplicacion}</code>: nombre actual del sistema.</li><li><code>{minutos}</code>: tiempo durante el que funcionará el enlace.</li></ul><p class="mb-0"><strong>Ejemplo:</strong> Hola <code>{nombre}</code>, el enlace de <code>{aplicacion}</code> vencerá en <code>{minutos}</code> minutos.</p></div></div></div>
                        </div>
                    </div>
                    <div class="tab-pane {{ $tabModuloActivo === 'backups' ? 'active show' : '' }}" id="correo-backups" role="tabpanel">
                        <div class="mb-4"><label for="backup_destinatarios" class="form-label">Personas que recibirán los avisos</label><textarea id="backup_destinatarios" name="backup_destinatarios" rows="3" class="form-control @error('backup_destinatarios') is-invalid @enderror" required maxlength="2000" @disabled(! $puedeEditar)>{{ $destinatarios }}</textarea><div class="form-text">Puedes escribir hasta 10 direcciones, una por línea.</div>@error('backup_destinatarios')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        <div class="row g-3 align-items-stretch">
                            <div class="col-lg-6" data-backup-preview="backup_exito"><div class="border rounded p-3 h-100">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3"><h6 class="text-success mb-0"><i class="ri-checkbox-circle-line me-1"></i>Resultado correcto</h6><div class="form-check form-switch mb-0"><input type="hidden" name="backup_notificar_exito" value="0"><input class="form-check-input" type="checkbox" id="backup_notificar_exito" name="backup_notificar_exito" value="1" @checked(old('backup_notificar_exito', $configuracion->backup_notificar_exito)) @disabled(! $puedeEditar)><label class="form-check-label" for="backup_notificar_exito">Enviar aviso</label></div></div>
                                <div class="form-check form-switch border-bottom pb-3 mb-3"><input type="hidden" name="backup_adjuntar_archivo" value="0"><input class="form-check-input" type="checkbox" id="backup_adjuntar_archivo" name="backup_adjuntar_archivo" value="1" @checked(old('backup_adjuntar_archivo', $configuracion->backup_adjuntar_archivo)) @disabled(! $puedeEditar)><label class="form-check-label" for="backup_adjuntar_archivo">Adjuntar archivo de la base de datos</label></div>
                                <div class="mb-3"><label for="backup_asunto_exito" class="form-label">Asunto</label><input type="text" id="backup_asunto_exito" name="backup_asunto_exito" class="form-control @error('backup_asunto_exito') is-invalid @enderror" value="{{ old('backup_asunto_exito', $configuracion->backup_asunto_exito) }}" required maxlength="150" @disabled(! $puedeEditar)>@error('backup_asunto_exito')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                <label for="backup_mensaje_exito" class="form-label">Mensaje</label><textarea id="backup_mensaje_exito" name="backup_mensaje_exito" rows="5" class="form-control @error('backup_mensaje_exito') is-invalid @enderror" required maxlength="1500" @disabled(! $puedeEditar)>{{ old('backup_mensaje_exito', $configuracion->backup_mensaje_exito) }}</textarea>@error('backup_mensaje_exito')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div></div>
                            <div class="col-lg-6" data-backup-preview="backup_error"><div class="border rounded p-3 h-100">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3"><h6 class="text-danger mb-0"><i class="ri-error-warning-line me-1"></i>Resultado con error</h6><div class="form-check form-switch mb-0"><input type="hidden" name="backup_notificar_error" value="0"><input class="form-check-input" type="checkbox" id="backup_notificar_error" name="backup_notificar_error" value="1" @checked(old('backup_notificar_error', $configuracion->backup_notificar_error)) @disabled(! $puedeEditar)><label class="form-check-label" for="backup_notificar_error">Enviar aviso</label></div></div>
                                <div class="mb-3"><label for="backup_asunto_error" class="form-label">Asunto</label><input type="text" id="backup_asunto_error" name="backup_asunto_error" class="form-control @error('backup_asunto_error') is-invalid @enderror" value="{{ old('backup_asunto_error', $configuracion->backup_asunto_error) }}" required maxlength="150" @disabled(! $puedeEditar)>@error('backup_asunto_error')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                <label for="backup_mensaje_error" class="form-label">Mensaje</label><textarea id="backup_mensaje_error" name="backup_mensaje_error" rows="5" class="form-control @error('backup_mensaje_error') is-invalid @enderror" required maxlength="1500" @disabled(! $puedeEditar)>{{ old('backup_mensaje_error', $configuracion->backup_mensaje_error) }}</textarea>@error('backup_mensaje_error')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div></div>
                        </div>
                        <div class="alert alert-info mt-3 mb-0 d-flex gap-3"><i class="ri-information-line fs-22 flex-shrink-0"></i><div><h6 class="alert-heading mb-2">Datos que puedes insertar automáticamente</h6><ul class="mb-2 ps-3"><li><code>{aplicacion}</code>: nombre actual del sistema.</li><li><code>{fecha}</code>: fecha y hora del resultado.</li><li><code>{estado}</code>: indica si el resultado fue correcto o presentó un error.</li><li><code>{detalle}</code>: información técnica disponible cuando ocurre un error.</li></ul><p class="mb-0"><strong>Ejemplo:</strong> La copia de <code>{aplicacion}</code> terminó el <code>{fecha}</code>. Detalle: <code>{detalle}</code>.</p></div></div>
                    </div>
                </div></div>
            </div>
            @if ($puedeEditar)<div class="d-flex justify-content-end mt-4"><button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i>Guardar cambios</button></div>@endif
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script src="{{ asset('assets/libs/filepond/filepond.min.js') }}"></script>
<script src="{{ asset('assets/libs/filepond-plugin-file-validate-size/filepond-plugin-file-validate-size.min.js') }}"></script>
<script src="{{ asset('assets/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const ejemplos = @json($valoresEjemplo), logoOriginal = @json($logoVista);
    let modo = 'apariencia', logoTemporal = null;
    const el = id => document.getElementById(id), val = id => el(id)?.value || '';
    const completar = (texto, datos = ejemplos) => { let resultado = texto || ''; Object.entries(datos).forEach(([k, v]) => resultado = resultado.split(k).join(v)); return resultado; };
    const datosPreview = {
        apariencia: () => ['Apariencia general', 'ri-mail-line', '', 'Título del mensaje', 'Así se verán los correos enviados por el sistema.', 'Acción del correo', 'normal'],
        verificacion: () => ['Verificación de correo', 'ri-mail-check-line', val('verificacion_asunto'), completar(val('verificacion_titulo')), completar(val('verificacion_mensaje')), val('verificacion_boton'), 'normal'],
        recuperacion: () => ['Recuperación de contraseña', 'ri-lock-password-line', val('recuperacion_asunto'), completar(val('recuperacion_titulo')), completar(val('recuperacion_mensaje')), val('recuperacion_boton'), 'normal'],
        backup_exito: () => ['Copia finalizada', 'ri-checkbox-circle-line', val('backup_asunto_exito'), completar(val('backup_asunto_exito')), completar(val('backup_mensaje_exito')), '', 'exito'],
        backup_error: () => ['Error en la copia', 'ri-close-circle-line', val('backup_asunto_error'), completar(val('backup_asunto_error')), completar(val('backup_mensaje_error'), {...ejemplos, '{estado}':'error', '{detalle}':'No fue posible completar la copia.'}), '', 'error']
    };
    const render = (nuevo = modo) => {
        modo = nuevo; const [etiqueta, clase, asunto, titulo, mensaje, boton, estado] = (datosPreview[modo] || datosPreview.apariencia)(); const color = val('color_primario') || @json($colorVistaSeguro);
        el('preview_tipo').textContent = etiqueta; el('preview_marca').textContent = val('nombre_marca'); el('preview_encabezado').style.backgroundColor = color; el('preview_asunto').textContent = asunto ? `Asunto: ${asunto}` : ''; el('preview_titulo').textContent = titulo; el('preview_mensaje').textContent = mensaje;
        el('preview_icono').className = `${clase} fs-36 d-inline-block mb-2`; el('preview_icono').style.color = estado === 'exito' ? '#0ab39c' : estado === 'error' ? '#f06548' : color; el('preview_boton').textContent = boton; el('preview_boton').style.backgroundColor = color; el('preview_boton').classList.toggle('d-none', !boton);
    };
    document.querySelectorAll('[data-preview-mode]').forEach(tab => tab.addEventListener('shown.bs.tab', () => render(tab.dataset.previewMode)));
    document.querySelectorAll('[data-backup-preview]').forEach(b => b.addEventListener('focusin', () => render(b.dataset.backupPreview)));
    document.querySelectorAll('input[type="text"],input[type="color"],textarea').forEach(c => c.addEventListener('input', () => render()));
    ['nombre_marca','color_primario'].forEach(id => el(id)?.addEventListener('focus', () => render('apariencia')));
    const actualizarLogo = url => { if (url) { el('preview_logo').src = url; el('preview_logo').classList.remove('d-none'); } else { el('preview_logo').removeAttribute('src'); el('preview_logo').classList.add('d-none'); } };
    const inputLogo = el('logo');
    if (inputLogo && !inputLogo.disabled && typeof FilePond !== 'undefined') {
        const plugins = [];
        if (typeof FilePondPluginFileValidateSize !== 'undefined') plugins.push(FilePondPluginFileValidateSize);
        if (typeof FilePondPluginImagePreview !== 'undefined') plugins.push(FilePondPluginImagePreview);
        if (plugins.length) FilePond.registerPlugin(...plugins);
        FilePond.create(inputLogo, {allowMultiple:false,storeAsFile:true,credits:false,acceptedFileTypes:['image/png'],maxFileSize:'5MB',imagePreviewHeight:150,labelIdle:'Arrastra tu PNG aquí o <span class="filepond--label-action">selecciónalo</span>',labelMaxFileSizeExceeded:'La imagen supera el tamaño permitido',labelMaxFileSize:'El tamaño máximo es {filesize}',beforeAddFile:item=>{const archivo=item.file;if(archivo?.type==='image/png'&&archivo.size<=5242880)return true;if(typeof Swal!=='undefined')Swal.fire({title:'Imagen no válida',text:'Selecciona una imagen PNG de hasta 5 MB.',icon:'error',confirmButtonText:'Aceptar'});return false;},onaddfile:(error,item)=>{if(error||!item?.file)return;const lector=new FileReader();lector.onload=evento=>{logoTemporal=evento.target.result;actualizarLogo(logoTemporal);render('apariencia');};lector.readAsDataURL(item.file);},onremovefile:()=>{logoTemporal=null;actualizarLogo(logoOriginal);}});
    }
    render(@json($tabModuloActivo === 'backups' ? 'backup_exito' : $tabModuloActivo));
});
</script>
@endpush
