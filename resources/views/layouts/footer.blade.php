<footer class="footer">
  <div class="container" style="overflow: hidden; height: 100%">
    <div class="d-flex justify-content-between">
      <div>
        <p class="text-muted text-right"><a class="text-muted" href="https://github.com/RoboJackets/apiary">Made with ♥ by RoboJackets</a> • <a class="text-muted" href="/privacy">Privacy Policy</a></p>
      </div>
      <div class="text-muted">
        <a class="text-muted text-left" target="_blank" href="https://docs.google.com/forms/d/e/1FAIpQLSelERsYq3gLmHbWvVCWha5iCU8z3r9VYC0hCN4ArLpMAiysaQ/viewform?entry.1338203640={{ $request->fullUrl()}}">Make a Wish</a>
@if(SentrySdk::getCurrentHub()->getSpan() !== null && \Sentry\SentrySdk::getCurrentHub()->getSpan()->getSampled())
@if(auth()->user() && auth()->user()->hasRole('admin'))
• <a class="text-muted" href="https://sentry.io/organizations/robojackets/performance/trace/{{ \Sentry\SentrySdk::getCurrentHub()->getSpan()->getTraceId() }}">Trace ID: {{ \Sentry\SentrySdk::getCurrentHub()->getSpan()->getTraceId() }}</a>
@else
• Trace ID: {{ \Sentry\SentrySdk::getCurrentHub()->getSpan()->getTraceId() }}
@endif
@endif
      </div>
    </div>
  </div>
</footer>
