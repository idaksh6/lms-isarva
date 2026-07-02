@props([
    'assignment' => null,
])

@php
    use App\Enums\SubmissionDeliveryMethod;

    $selectedMethod = old('delivery_method', $assignment?->delivery_method?->value ?? SubmissionDeliveryMethod::File->value);
@endphp

<div x-data="{ method: @js($selectedMethod) }" class="space-y-0">
    <div class="lms-form-field">
        <label for="delivery_method" class="lms-field-label">How students submit work</label>
        <select
            id="delivery_method"
            name="delivery_method"
            class="lms-field-input mt-1.5"
            x-model="method"
            required
        >
            @foreach (SubmissionDeliveryMethod::cases() as $option)
                <option value="{{ $option->value }}" @selected($selectedMethod === $option->value)>
                    {{ $option->label() }}
                </option>
            @endforeach
        </select>
        <p class="mt-1.5 text-xs text-isarva-muted">
            Choose <strong>Cloud link</strong> when students upload large zip bundles to a shared Google Drive folder and paste the file link here.
        </p>
        <x-input-error :messages="$errors->get('delivery_method')" class="mt-1.5" />
    </div>

    <div class="lms-form-field" x-show="method === 'link' || method === 'both'" x-cloak>
        <label for="drop_folder_url" class="lms-field-label">Shared upload folder (for students)</label>
        <input
            id="drop_folder_url"
            type="url"
            name="drop_folder_url"
            value="{{ old('drop_folder_url', $assignment?->drop_folder_url) }}"
            class="lms-field-input mt-1.5"
            placeholder="https://drive.google.com/drive/folders/..."
            :required="method === 'link' || method === 'both'"
        >
        <p class="mt-1.5 text-xs text-isarva-muted">
            Create a Google Drive folder, share it with your class, and paste the folder link. Students upload their zip there, then submit the individual file link in the LMS.
        </p>
        <x-input-error :messages="$errors->get('drop_folder_url')" class="mt-1.5" />
    </div>
</div>
