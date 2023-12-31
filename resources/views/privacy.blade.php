@extends('layouts/app')

@section('title')
    Privacy Policy | {{ config('app.name') }}
@endsection

@section('content')

    @component('layouts/title')
        Privacy Policy
    @endcomponent

    <div class="row">
        <div class="col-md-12">
            <p>Your use of this application is primarily governed by the <a href="http://www.gatech.edu/privacy">Georgia Tech Privacy and Legal Notice</a>. This document describes how RoboJackets will handle your information internally and under what circumstances it may be shared outside of RoboJackets.</p>

            <h2>What data does RoboJackets collect?</h2>
            <p>RoboJackets stores the following information about you each time you log in, as reported by the Georgia Tech Enterprise Directory. You may opt out of this data collection by not using the application.</p>
            <ul>
                <li>Your first name</li>
                <li>Your last name</li>
                <li>Your primary Georgia Tech email address</li>
                <li>Your GTID number</li>
                <li>Your Georgia Tech username</li>
                <li>Your major(s)</li>
                <li>Your class standing</li>
                <li>Your Georgia Tech employment information, if applicable, including your employee ID and home department</li>
                <li>Degrees awarded to you by Georgia Tech</li>
                <li>Information supplied by your web browser, including (but not limited to) your IP address, web browser version, operating system, and device manufacturer and/or model number</li>
            </ul>
            <p>RoboJackets may store the following information about you, if you voluntarily provide it. You may opt out of this data collection by declining to provide it.</p>
            <ul>
                <li>Your personal email address</li>
                <li>Your preferred name</li>
                <li>Your phone number</li>
                <li>Your emergency contact information, including their name and phone number</li>
                <li>Your shirt and polo sizes</li>
                <li>Your expected graduation date</li>
                <li>Your resume</li>
                <li>Identifiers, such as username or email, of your linked Github, Google, ClickUp, and Autodesk accounts</li>
                <li>Transaction identifiers for credit/debit card transactions completed by RoboJackets' payments processor, Square; RoboJackets does not process or store billing data, including credit/debit card numbers.</li>
                <li>A history of events and team meetings attended</li>
                <li>Your parent or legal guardian's name and email address</li>
            </ul>

            <h2>How will RoboJackets use your data?</h2>
            <ul>
                <li>To track membership activity, including dues transactions and meeting attendance</li>
                <li>To facilitate payments via credit and debit cards</li>
                <li>To purchase and distribute items included with membership, such as t-shirts and polos</li>
                <li>To provision and manage access to computer systems that RoboJackets uses or maintains, such as Google Drive, RoboJackets Cloud, GitHub, ClickUp, SUMS, and WordPress</li>
                <li>To generate analytical reports regarding membership engagement and composition</li>
                <li>To email you regarding your membership in RoboJackets, including in advance of and for changes in membership or access status</li>
                <li>To maintain the RoboJackets resume book, which is provided to sponsors seeking to hire our members</li>
                <li>To access your emergency contacts, if provided, in the event of an incident while conducting RoboJackets activities</li>
                <li>To request Georgia Tech's Institute Approved Absence forms on your behalf for qualifying activities</li>
            </ul>

            <h2>How does RoboJackets store your data?</h2>
            <p>Data collected by RoboJackets is stored on a server secured within a Georgia Tech datacenter and managed by the Georgia Tech Office of Information Technology.</p>

            <h2>How will RoboJackets share your data?</h2>
            <p>RoboJackets shares data with certain external organizations in the course of conducting day to day business.</p>
            <ul>
                <li>Identifying information, including, but not limited to, your GTID and name, will be shared as strictly necessary to Georgia Tech campus departments performing work on our behalf, including, but not limited to, granting BuzzCard access to the shop, enrollment in SUMS, obtaining Institute Approved Absence letters, and reimbursement processing.</li>
                <li>Resumes of all active members will be shared with our sponsors. You can opt out of this by not submitting a resume on this website.</li>
                <li>Attendance data will be shared in aggregate or with identifying information to Georgia Tech campus departments.</li>
                <li>Demographic information will be shared in aggregate to current and potential sponsors.</li>
                <li>Information regarding an individual payment, including your Georgia Tech email address and phone number, will be shared with Square, RoboJackets' designated payments processor, for the purposes of completing a credit/debit card transaction. You can opt-out of this sharing by using another payment method.
                <li>Your Georgia Tech username and information about your device, as reported by your web browser or RoboJackets Android application, may be shared with Sentry, a product of Functional Software, Inc, to help us troubleshoot issues you may experience while using RoboJackets services.</li>
                <li>Your name and Georgia Tech email address may be shared with Postmark, a product of ActiveCampaign, LLC, to deliver emails, including, but not limited to, reminders and payment receipts.</li>
                <li>Your name, Georgia Tech email address, Georgia Tech employee ID, Georgia Tech employment home department, phone number, parent or legal guardian name, and parent or legal guardian email address may be shared with DocuSign, a product of DocuSign, Inc, to enable you to sign documents electronically.</li>
                <li>All other information will be released only to RoboJackets officers and officer-designated RoboJackets members in the course of conducting business.</li>
            </ul>
            <p>RoboJackets does not sell any of the information collected. However, sponsors who provide financial support to RoboJackets will receive aggregated data as outlined above as a benefit of their support.</p>

            <h2>Cookies</h2>
            <p>This application uses cookies to keep you logged-in during and between sessions. If you elect to disable first-party cookies in your browser settings, this website may no longer function as intended.</p>

            <h2>Privacy policies of other websites</h2>
            <p>This application contains links and redirects to other websites. This privacy policy applies only to this website, as the other websites are governed by their own privacy policies.</p>

            <h2>Changes to this privacy policy</h2>
            <p>RoboJackets IT reviews this privacy policy regularly and places updates on this webpage. This document was last updated on May 10th, 2023.</p>

            <h2>How to contact us</h2>
            <p>If you have a question regarding any RoboJackets computing systems, including this website, contact RoboJackets IT by emailing <a href="support@robojackets.org">support@robojackets.org</a>.</p>

            <h2>EU GDPR Compliance</h2>
            <p>RoboJackets members who are citizens of the European Union have additional rights granted to them under the European Union's General Data Protection Regulation (GDPR). These individuals may exercise their rights under this regulation by emailing <a href="mailto:gdpr@robojackets.org">gdpr@robojackets.org</a> with their request.</p>
        </div>
    </div>
@endsection
