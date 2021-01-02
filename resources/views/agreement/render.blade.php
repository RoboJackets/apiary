@extends('layouts/app')

@section('title')
Membership Agreement | {{ config('app.name') }}
@endsection

@section('content')
@component('layouts/title')
  Membership Agreement
@endcomponent

<p>
Revised {{ $updated_at }}
</p>

@markdown($text)

@if ($errors->any())
            @foreach ($errors->all() as $error)
    <div class="alert alert-danger" role="alert">{{ $error }}</div>
            @endforeach
@endif

<form method="POST" action="{{ route('agreement.redirect') }}">
@csrf
<div class="form-check">
<input type="checkbox" class="form-check-input" id="electronicSignatureConsent" name="eSignConsent" required>
<label class="form-check-label" for="electronicSignatureConsent">I consent to signing this document electronically. I understand that I can print a paper copy at <a href="{{ route('agreement.print') }}">this link</a> and submit it to a project manager or officer if I prefer not to sign electronically.</label>
</div>
<div class="form-check">
<input type="checkbox" class="form-check-input" id="acknowledgement" name="acknowledgement" required>
<label class="form-check-label" for="acknowledgement">I have read the above membership agreement and agree to it. I understand that clicking "Submit" below will send me to Georgia Tech Login, where I will need to log in again to electronically sign this document.</label>
</div>
<br>
<button class="btn btn-primary" type="submit">Submit</button>
</form>

@endsection
