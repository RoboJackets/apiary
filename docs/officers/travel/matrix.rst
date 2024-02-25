:og:description: Apiary uses the Matrix JSON format for itineraries to ensure the booking agent has enough information to book the desired flights. Officers can also configure policies to ensure itineraries meet best practices.

Matrix airfare search
=====================

Apiary uses the Matrix JSON format for itineraries to ensure the booking agent has enough information to book the desired flights.
Officers can also configure policies to ensure itineraries meet best practices.

Enable advanced controls
------------------------

.. vale Google.Passive = NO
.. vale write-good.E-Prime = NO
.. vale write-good.Passive = NO

Apiary includes a simplified interface to search for itineraries that meet the policy configured for a trip.
To use this feature, ensure advanced controls are enabled within Matrix.

#. Open `Matrix <https://matrix.itasoftware.com/search>`_.
#. Ensure that the :guilabel:`Routing Codes` and :guilabel:`Extension Codes` fields are shown under the :guilabel:`Origin` and :guilabel:`Destination` fields.
#. If not, click the :guilabel:`Show Advanced Controls` button.

After advanced controls are enabled, you can use the :guilabel:`Matrix Airfare Search` action on your trip.

.. vale Google.Passive = YES
.. vale write-good.E-Prime = YES
.. vale write-good.Passive = YES

Search for flights
------------------

.. vale Google.Will = NO

#. In Apiary, on the :guilabel:`Trip Details` page, click the Actions menu (three dots |actionsmenu|) to the right of the :guilabel:`Trip Details` header.
#. Select :guilabel:`Matrix Airfare Search`.
#. A popup will appear with options to configure your search.
#. After filling out the popup, click the :guilabel:`Search` button at the bottom.
   This will open Matrix in a new tab with several search criteria pre-populated.
   You can made adjustments on this page if desired, but the results might not meet the airfare policy for your trip.
#. To submit your search, click the blue :guilabel:`Search` button in the bottom right in Matrix.
   This will show all flights that meet your criteria.
   You can change the results layout using the tabs at the top of the results.
#. To select flights, click the price in the left column.

Copy itineraries to trip assignments
------------------------------------

1. On the :guilabel:`Itinerary Details` page in Matrix, click the :guilabel:`Copy itinerary as JSON` option in the right sidebar.
2. You can then paste the JSON into the :guilabel:`Matrix Itinierary` field on a trip assignment in Apiary.

If you made changes to the search criteria either in Apiary or Matrix, your selected itinerary may not meet the airfare policy configured for your trip.
Officers can adjust the airfare policy if needed.
