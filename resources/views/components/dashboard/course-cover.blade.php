@props(['course', 'class' => '', 'animated' => true, 'large' => false])

@php
    use App\Support\CourseCoverImage;
@endphp

<div @class([
    'dashboard-cover-motion',
    'dashboard-cover-motion--static' => ! $animated,
    'dashboard-cover-motion--large' => $large,
    $class,
])>
    <img
        src="{{ CourseCoverImage::url($course) }}"
        srcset="{{ CourseCoverImage::srcset($course) }}"
        sizes="{{ $large ? '(min-width: 1280px) 400px, (min-width: 640px) 50vw, 100vw' : '96px' }}"
        alt="{{ CourseCoverImage::alt($course) }}"
        loading="lazy"
        decoding="async"
        class="dashboard-course-cover-img"
    >
</div>
