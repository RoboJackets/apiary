<footer class="footer">
  <div class="container" style="overflow: hidden; height: 100%">
    <div class="d-flex justify-content-between">
      <div>
        <p class="text-footer text-end"><a class="text-footer" href="https://github.com/RoboJackets/apiary">Made with ♥ by RoboJackets</a> • <a class="text-footer" href="/privacy">Privacy Policy</a></p>
      </div>
      <div class="text-footer">
@if(\Sentry\SentrySdk::getCurrentHub()->getSpan() !== null && \Sentry\SentrySdk::getCurrentHub()->getSpan()->getSampled())
@if(auth()->user() && auth()->user()->hasRole('admin'))
<a class="text-footer" href="https://sentry.io/organizations/robojackets/performance/trace/{{ \Sentry\SentrySdk::getCurrentHub()->getSpan()->getTraceId() }}">Trace ID: {{ \Sentry\SentrySdk::getCurrentHub()->getSpan()->getTraceId() }}</a>
@else
Trace ID: {{ \Sentry\SentrySdk::getCurrentHub()->getSpan()->getTraceId() }}
@endif
@endif
      </div>
    </div>
  </div>
</footer>
