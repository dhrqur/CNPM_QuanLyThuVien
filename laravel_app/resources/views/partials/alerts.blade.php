@if (session('success'))
    <div class="alert alert-success rounded-4">{{ session('success') }}</div>
@endif

@if (session('danger'))
    <div class="alert alert-danger rounded-4">{{ session('danger') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger rounded-4">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
