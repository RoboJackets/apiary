@extends('layouts/app')

@section('title')
Membership Agreement | {{ config('app.name') }}
@endsection

@section('content')
<br>

@markdown($text)

<hr>

@if ($errors->any())
            @foreach ($errors->all() as $error)
    <div class="alert alert-danger" role="alert">{{ $error }}</div>
            @endforeach
@endif

<form method="POST" action="{{ route('agreement.redirect') }}">
@csrf
<div class="form-check">
<input type="checkbox" class="form-check-input" id="over18" name="over18" required>
<label class="form-check-label" for="over18">I am over 18 years of age.</label>
</div>
<div class="form-check">
<input type="checkbox" class="form-check-input" id="electronicSignatureConsent" name="eSignConsent" required>
<label class="form-check-label" for="electronicSignatureConsent">I consent to signing this document electronically. I understand that I can print a paper copy at <a href="{{ route('agreement.print') }}">this link</a> and submit it to a project manager or officer if I prefer not to sign electronically.</label>
</div>
<div class="form-check">
<input type="checkbox" class="form-check-input" id="acknowledgement" name="acknowledgement" required>
<label class="form-check-label" for="acknowledgement">I have read and agree to the above membership agreement. I understand that clicking "Submit" below will send me to Georgia Tech Login, where I will need to log in again to electronically sign this document.</label>
</div>
<br>
<button class="btn btn-primary" type="submit">Submit</button>
</form>
<br><br><br>

@endsection
