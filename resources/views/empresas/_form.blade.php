@php $empresa = $empresa ?? null; @endphp

<div class="grid grid-cols-1 gap-5">
    <div>
        <x-input-label for="nombre" value="Nombre *" />
        <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full"
            value="{{ old('nombre', $empresa->nombre ?? '') }}" required autofocus />
        <x-input-error :messages="$errors->get('nombre')" class="mt-1" />
    </div>
    <div>
        <x-input-label for="razon_social" value="Razón social *" />
        <x-text-input id="razon_social" name="razon_social" type="text" class="mt-1 block w-full"
            value="{{ old('razon_social', $empresa->razon_social ?? '') }}" required />
        <x-input-error :messages="$errors->get('razon_social')" class="mt-1" />
    </div>
    <div>
        <x-input-label for="cuit" value="CUIT *" />
        <x-text-input id="cuit" name="cuit" type="text" class="mt-1 block w-full font-mono"
            value="{{ old('cuit', $empresa->cuit ?? '') }}" placeholder="20-12345678-9" required />
        <x-input-error :messages="$errors->get('cuit')" class="mt-1" />
    </div>
</div>
