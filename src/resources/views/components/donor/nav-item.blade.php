@props([
    'route',
    'icon',
    'label',
    'mobile' => false,
])

@php
    $active = request()->routeIs($route);

    $classes = $mobile
        ? 'donor-mobile-nav-link'
        : 'donor-nav-item';

    if ($active) {
        $classes .= ' is-active';
    }
@endphp

<a
    href="{{ route($route) }}"
    wire:navigate
    {{ $attributes->class($classes) }}
>
    <x-donor.icon :name="$icon" />

    <span>{{ $label }}</span>
</a>