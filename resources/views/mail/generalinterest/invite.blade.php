@component('mail::message')
_You are receiving this email because you expressed interest in joining RoboJackets, the student organization for competitive robotics at Georgia Tech._

Hello future RoboJackets,

If you are a first-year student starting your journey at Georgia Tech, RoboJackets would like to welcome you to the GT family. Your time here will be full of excitement and challenge, and we hope RoboJackets will be a part of that experience.

# How to join
To better acquaint you with our teams, we would like to invite you to our General Interest (GI) events. Each GI session will give you the details of our teams’ activities and how to get involved.

There will be **free pizza** and lots of robots!

The two sessions will cover the same content, so you should only attend one of the two. Both will be hosted in [Howey L3](https://goo.gl/fJGRNe).

# RSVP
Please RSVP to the session you want to attend using the link below so we know how many people to expect.

@component('mail::button', ['url' => $app_url . "/events/7/rsvp?source=" . $visit_token])
    RSVP for Tuesday, September 4 (6-8pm)
@endcomponent

@component('mail::button', ['url' => $app_url . "/events/8/rsvp?source=" . $visit_token])
    RSVP for Wednesday, September 5 (6-8pm)
@endcomponent

Don't worry if you can't make it to either of these dates. We'll be sending out another email afterwards with all of the information we presented.

I look forward to seeing you all next week! If you have any questions in the meantime, please feel free to contact our leadership team at [hello@robojackets.org](mailto:hello@robojackets.org).

Thank you for your interest in joining RoboJackets!

Jason Gibson<br/>RoboJackets President
@endcomponent
