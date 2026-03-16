<div class="accordion" id="{{ $id }}">
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $index }}"
                aria-expanded="true" aria-controls="{{ $index }}">
                {{ $title }}
            </button>
        </h2>
        <div id="{{ $index }}" class="accordion-collapse collapse show" data-bs-parent="{{ $id }}>">
            <div class="accordion-body">
                {{ $body }}
            </div>
        </div>
    </div>
</div>
