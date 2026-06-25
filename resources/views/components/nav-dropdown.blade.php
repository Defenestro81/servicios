@props(['title', 'active' => false])

@php
$base = 'inline-flex items-center h-16 px-1 border-b-2 text-sm font-medium leading-5 focus:outline-none transition duration-150 ease-in-out';
$state = $active
    ? 'border-indigo-400 text-gray-900'
    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300';
@endphp

<x-dropdown align="left" width="w-56">
    <x-slot name="trigger">
        <button type="button" class="{{ $base }} {{ $state }}">
            {{ $title }}
            <svg class="ms-1 h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>
    </x-slot>
    <x-slot name="content">
        {{ $slot }}
    </x-slot>
</x-dropdown>
