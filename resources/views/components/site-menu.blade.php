@props([
    'slug' => null,
    'area' => null,
    'class' => '',
    'header' => true,
])

<x-cms-menu :slug="$slug" :area="$area" :class="$class" :header="$header" />

