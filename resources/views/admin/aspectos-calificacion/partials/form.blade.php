@php
    $aspectoCalificacion = $aspectoCalificacion ?? null;
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
        <input
            type="text"
            class="form-control @error('nombre') is-invalid @enderror"
            id="nombre"
            name="nombre"
            value="{{ old('nombre', $aspectoCalificacion?->nombre) }}"
            placeholder="Ejemplo: Puntualidad"
            required
        >
        @error('nombre')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @else
            <div class="invalid-feedback">Ingresa el nombre del aspecto.</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="estado" class="form-label">Estado <span class="text-danger">*</span></label>
        <select class="form-select @error('estado') is-invalid @enderror" id="estado" name="estado" required>
            <option value="1" @selected((string) old('estado', (int) ($aspectoCalificacion?->estado ?? true)) === '1')>Activo</option>
            <option value="0" @selected((string) old('estado', (int) ($aspectoCalificacion?->estado ?? true)) === '0')>Inactivo</option>
        </select>
        @error('estado')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label for="descripcion" class="form-label">Descripción</label>
        <textarea
            class="form-control @error('descripcion') is-invalid @enderror"
            id="descripcion"
            name="descripcion"
            rows="4"
            placeholder="Describe qué mide este aspecto."
        >{{ old('descripcion', $aspectoCalificacion?->descripcion) }}</textarea>
        @error('descripcion')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>
