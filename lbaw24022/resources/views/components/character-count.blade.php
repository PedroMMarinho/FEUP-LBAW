@props(['targetId', 'maxLength' => 700])

<div id="{{ $targetId }}_character_count" class="text-end mt-1">
    0/{{ $maxLength }} characters used
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const targetElement = document.getElementById('{{ $targetId }}');
        const characterCountElement = document.getElementById('{{ $targetId }}_character_count');

        if (targetElement && characterCountElement) {
            targetElement.addEventListener('input', () => {
                const currentLength = targetElement.value.length;
                characterCountElement.textContent = `${currentLength}/{{ $maxLength }} characters`;

                characterCountElement.style.color = currentLength > {{ $maxLength }} ? 'red' : 'black';
            });

            // Initialize count
            const initialLength = targetElement.value.length || 0;
            characterCountElement.textContent = `${initialLength}/{{ $maxLength }} characters`;
        }
    });
</script>
@endpush
