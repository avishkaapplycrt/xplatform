@props([
    'name',
    'value',
    'label',
    'description' => null,
    'checked' => false,
])

<label class="flex items-start gap-3 border rounded-xl px-4 py-3 cursor-pointer transition
              has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50
              border-gray-200 bg-white hover:border-indigo-300">
    <input type="checkbox"
           name="{{ $name }}"
           value="{{ $value }}"
           class="w-4 h-4 mt-0.5 accent-indigo-600 shrink-0"
           @checked($checked)>
    <span class="min-w-0">
        <span class="block text-sm font-medium text-gray-800">{{ $label }}</span>
        @if($description)
            <span class="block text-xs text-gray-500 mt-0.5">{{ $description }}</span>
        @endif
    </span>
</label>
