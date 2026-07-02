@php
    $activeTheme = $lmsTheme ?? \App\Support\LmsTheme::resolve(null);
@endphp
<style>
    :root {
        {!! \App\Support\LmsTheme::cssVariables($activeTheme['key']) !!}
    }
</style>
