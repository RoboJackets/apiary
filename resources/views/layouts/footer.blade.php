<footer class="footer">
  <div class="container" style="overflow: hidden; height: 100%">
    <div class="d-flex justify-content-between">
      <div>
        <p class="text-muted text-right"><a class="text-muted" href="https://github.com/RoboJackets/apiary">Made with ♥ by RoboJackets</a> • <a class="text-muted" href="/privacy">Privacy Policy</a></p>
      </div>
      <div>
        <p class="text-muted">
        <a class="text-muted text-left" target="_blank" href="https://docs.google.com/forms/d/e/1FAIpQLSelERsYq3gLmHbWvVCWha5iCU8z3r9VYC0hCN4ArLpMAiysaQ/viewform?entry.1338203640={{ $request->fullUrl()}}">Make a Wish</a>
@if(\Sentry\Laravel\Integration::currentTracingSpan() !== null)
@if(auth()->user() && auth()->user()->hasRole('admin'))
• <a class="text-muted" href="https://sentry.io/organizations/robojackets/performance/trace/{{ \Sentry\Laravel\Integration::currentTracingSpan()->getTraceId() }}">Trace ID: {{ \Sentry\Laravel\Integration::currentTracingSpan()->getTraceId() }}</a>
@else
• <p class="text-muted">Trace ID: {{ \Sentry\Laravel\Integration::currentTracingSpan()->getTraceId() }}</p>
@endif
@endif
</p>
      </div>
    </div>
  </div>
</footer>
