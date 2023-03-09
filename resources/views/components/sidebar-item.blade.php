@props(['active', 'url', 'icon'])

@php
$classes = ($active ?? false)
            ? 'active'
            : '';
@endphp

<li class="nav-item">
    <a href="{{ $url }}" class="nav-link {{ $classes }}">
        <i class="nav-icon fas {{$icon}}"></i>
        <p>
        {{ $slot }}
        </p>
    </a>
</li>
