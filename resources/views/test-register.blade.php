<!DOCTYPE html>
<html>
<head>
    <title>Test Register</title>
</head>
<body>
    <h1>Test Registration</h1>
    
    @if (isset($errors) && $errors && $errors->any())
        <div class="alert error">Please fix the fields highlighted below.</div>
    @endif
    
    <form method="POST" action="{{ route('register.store') }}">
        @csrf
        <input type="hidden" name="role" value="client">
        
        <div>
            <label>Full name</label>
            <input name="full_name" value="{{ old('full_name') }}">
            @error('full_name')<div class="error">{{ $message }}</div>@enderror
        </div>
        
        <div>
            <label>Company name (optional)</label>
            <input name="company_name" value="{{ old('company_name') }}">
            @error('company_name')<div class="error">{{ $message }}</div>@enderror
        </div>
        
        <button type="submit">Register</button>
    </form>
</body>
</html>
