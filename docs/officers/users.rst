:og:description: Apiary represents individuals, both members and non-members, as User records that link to all other information associated with that individual. Officers can generate commonly used reports about members.

Users
=====

Apiary represents individuals, both members and non-members, as :guilabel:`User` records that link to all other information associated with that individual.
Officers can generate commonly used reports about members.

Export resumes
--------------

.. vale Google.Parens = NO
.. vale Google.Passive = NO
.. vale Google.Will = NO
.. vale proselint.Diacritical = NO
.. vale write-good.E-Prime = NO
.. vale write-good.Passive = NO
.. vale write-good.Weasel = NO

Apiary allows active members to upload a resume within the member-facing interface.
Officers can then generate a resume book to send to sponsors.

The resume book only includes active members.
Any filters applied to the user list outside of the popup are ignored.

.. hint::
   This export requires the ``read-users-resume`` permission, which is included in the :ref:`officer` and :ref:`admin` roles.
   If you need access, ask in :slack:`it-helpdesk`.

To export resumes:

#. From the Apiary homepage, click the :guilabel:`Admin` link in the top navigation bar.
#. Under the :guilabel:`Other` header in the left sidebar, click :guilabel:`Users`.
#. Click the Actions menu (three dots |actionsmenu|) in the top right corner, then select :guilabel:`Export Resumes`.
   A popup will appear.
#. Select the majors and class standings you want to include with this report, and the date cutoff to exclude old resumes.
   **Note that any filters selected outside of the popup are ignored.**
   The popup will only show majors and class standings for active members that have uploaded resumes.
#. Click :guilabel:`Export Resumes`, then wait.
   This may take several seconds depending on how many resumes are included.

All resumes that match the criteria are combined into a single :abbr:`PDF (Portable Document Format)` and downloaded.
Review the PDF before distributing it to sponsors.

.. vale Google.Headings = NO

Export BuzzCard access list
---------------------------

This function generates a :abbr:`CSV (comma-separated values)` file that Mechanical Engineering Facilities needs to grant BuzzCard access to the Student Competition Center and the RoboJackets shop.

.. hint::
   This export requires the ``read-users-gtid`` permission, which is only included in the :ref:`admin` role.
   If you need access, ask in :slack:`it-helpdesk`.

The CSV file includes members meeting the following criteria. Any filters applied to the user list are ignored.

* Must be access active
* Must be a current student, faculty, or staff, as determined by Georgia Tech
* Must not have paid for any dues packages that are **not** :guilabel:`Restricted to Students`
* Must not have the :guilabel:`BuzzCard Access Opt-Out` flag applied

To generate the list:

#. From the Apiary homepage, click the :guilabel:`Admin` link in the top navigation bar.
#. Under the :guilabel:`Other` header in the left sidebar, click :guilabel:`Users`.
#. Click the Actions menu (three dots |actionsmenu|) in the top right corner, then select :guilabel:`Export BuzzCard Access List`.
   A popup will appear.
#. Select the population that you want to export.
   The :guilabel:`Core` population only includes members within the Core team, and the :guilabel:`General` population only includes members that **aren't** within the Core team.
   You should generate lists for **both** populations and send **both** to Mechanical Engineering Facilities, but the export can only generate one at a time due to technical limitations.
#. Click :guilabel:`Export List`.

Export demographics survey recipients
-------------------------------------

This function generates a CSV file of email addresses that can be imported to Qualtrics for the annual demographics survey.

.. I'm not going to include an access hint here because it's restricted to read-users and that's included with all roles that have access to Nova.

The CSV file only includes active members without an email suppression. Any filters applied to the user list are ignored.

.. note::
   `Postmark <https://postmarkapp.com/>`_ applies an email suppression when an email address bounces, or if a user explicitly unsubscribes from Apiary emails.

To generate the list:

#. From the Apiary homepage, click the :guilabel:`Admin` link in the top navigation bar.
#. Under the :guilabel:`Other` header in the left sidebar, click :guilabel:`Users`.
#. Click the Actions menu (three dots |actionsmenu|) in the top right corner, then select :guilabel:`Export Demographics Survey Recipients`.
