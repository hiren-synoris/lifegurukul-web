<!-- Server side validation failed error message file -->
@if($errors->any())
    <div class="alert alert-danger p-3">
        <ul>
            @foreach($errors->all() as $key => $value)
                <li class="text-white">{{ $value }}</li>
            @endforeach
        </ul>
    </div>
@endif
