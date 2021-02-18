<html><body>
<h1>RoboJackets Resumes</h1>

<p>This file includes resumes of RoboJackets members in at least one of the following majors AND with one of the following class standings.</p>

<h2>Majors</h2>
<ul>
@foreach ($majors as $major)
    <li>{{ $major }}</li>
@endforeach
</ul>

<h2>Class Standings</h2>
<ul>
@foreach ($class_standings as $standing)
    <li>{{ ucfirst($standing) }}</li>
@endforeach
</ul>

<p>This collection was generated on <strong>{{ $generation_date }}</strong> and includes resumes uploaded after <strong>{{ $cutoff_date }}</strong>.</p>

<p>To request a new collection, please contact us at <a href="mailto:sponsors@robojackets.org">sponsors@robojackets.org</a>.</p>
</body></html>
