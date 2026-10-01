{{-- Debe usarse dentro de un contenedor con x-data="passwordCriteria" (ver resources/js/app.js). --}}
<ul {{ $attributes->merge(['class' => 'mt-2 space-y-1 text-sm']) }} aria-label="Criterios de la contraseña">
    @foreach (App\Support\PasswordCriteria::all() as $criterion)
        <li
            data-criterion="{{ $criterion['key'] }}"
            data-met="false"
            class="flex items-center gap-2 text-gray-500"
            x-bind:data-met="String(met['{{ $criterion['key'] }}'])"
            x-bind:class="met['{{ $criterion['key'] }}'] ? 'text-green-600' : 'text-gray-500'"
        >
            <span aria-hidden="true" x-text="met['{{ $criterion['key'] }}'] ? '✓' : '○'">○</span>
            <span>{{ $criterion['label'] }}</span>
        </li>
    @endforeach
</ul>
