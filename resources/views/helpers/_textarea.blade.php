<textarea 	@if (isset($id))
             id="{{ $id }}"
             @endif
             {{ isset($readonly) ? "readonly='readonly'" : "" }}
             type="text" name="{{ $name }}" class="form-control{{ $classes }} {{ isset($class) ? $class : "" }}">
    {{ isset($value) ? $value : "" }}
</textarea>
