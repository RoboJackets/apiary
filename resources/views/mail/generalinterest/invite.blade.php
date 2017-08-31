@component('mail::message')
Hello Future RoboJackets,

First, if you are one of the new freshmen starting your journey at Georgia Tech, RoboJackets would like to welcome you to the GT family. Your time here will be full of excitement and challenge, and we hope RoboJackets will be a part of that experience.

If you are receiving this email, you expressed interest in joining RoboJackets, the competitive robotics club at Georgia Tech.

# How to Join
RoboJackets will be holding two General Interest (GI) meetings that will give you the details of our teams’ activities and how to join. The two meetings cover the same content, and you should only attend one of the two.

# General Interest Meetings
The meetings will be held on Wednesday the 6th of September and Thursday the 7th of September. Both meetings are located in the [Howey Lecture Hall, L2](https://goo.gl/fJGRNe). There will be free pizza.

# RSVP
Please RSVP to the meeting you want to attend using the link below.

@component('mail::button', ['url' => $app_url . "/events/1/rsvp?source=" . $visit_token])
RSVP for Wednesday, September 6
@endcomponent

@component('mail::button', ['url' => $app_url . "/events/2/rsvp?source=" . $visit_token])
RSVP for Thursday, September 7
@endcomponent

I look forward to seeing you all next week. If you have any questions in the interim, feel free to reach out to [info@robojackets.org](mailto:info@robojackets.org).

Best,

Will Stuckey  
President, RoboJackets

@endcomponent
