@props([
    'course',
    'class' => '',
    'animated' => true,
    'large' => false,
    'variant' => 'corporate',
])

@php
    use App\Support\CourseCoverImage;
    use App\Support\CourseIllustration;

    $motif = CourseIllustration::variantFor($course->code);
    $palette = match ($motif) {
        'notebook' => 'lms-course-cover-corporate--slate',
        'analytics' => 'lms-course-cover-corporate--teal',
        'laptop' => 'lms-course-cover-corporate--indigo',
        default => 'lms-course-cover-corporate--brand',
    };
@endphp

@if ($variant === 'photo')
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
@else
    <div @class([
        'lms-course-cover-corporate',
        $palette,
        'lms-course-cover-corporate--lg' => $large,
        'lms-course-cover-corporate--sm' => ! $large,
        $class,
    ]) aria-hidden="true">
        <div class="lms-course-cover-corporate-grid"></div>
        <span class="lms-course-cover-corporate-code">{{ $course->code }}</span>
    </div>
@endif
