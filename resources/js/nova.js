import Vuelidate from 'vuelidate'
import * as Sentry from "@sentry/vue";
import { Integrations } from "@sentry/tracing";

Nova.booting((Vue, router) => {
    var sentryDsn = document.head.querySelector('meta[name="sentry-dsn"]').content;
    var sentryAppEnv = document.head.querySelector('meta[name="sentry-app-env"]').content;
    var sentryRelease = document.head.querySelector('meta[name="sentry-release"]').content;
    var sentryUserId = document.head.querySelector('meta[name="sentry-user-id"]');
    var sentryUsername = document.head.querySelector('meta[name="sentry-username"]');
    if (sentryDsn !== null) {
        if (sentryUserId !== null) {
            var initialScope = {
                user: {
                    id: sentryUserId.content,
                    username: sentryUsername.content,
                }
            }
        } else {
            var initialScope = {}
        }
        Sentry.init({
            Vue: Vue,
            dsn: sentryDsn,
            environment: sentryAppEnv,
            release: sentryRelease,
            initialScope: initialScope,
            attachProps: true,
            logErrors: true,
            integrations: [new Integrations.BrowserTracing()],
            tracesSampleRate: 1.0,
            tracingOptions: {
                trackComponents: true,
            },
        });
        window.Sentry = Sentry;
    } else {
        console.log('Sentry not loaded - DSN not present')
    }
})
