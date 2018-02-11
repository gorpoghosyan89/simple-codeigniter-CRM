<div class="text-content">
<h2>
    FMS-endpoint Help
</h2>

<p>
	FMS-Endpoint is a simple open source web application for storing problem reports created by mySociety's 
	FixMyStreet platform. In fact, as it's an Open311 server, it should be happy accepting any reports 
	submitted over the Open311 API.
</p>


<h3>Home</h3>

<p>The home page shows a summary of the current reports.</p>

<h3>Reports detail</h3>

<p>The Reports detail page shows a summary of the current reports in more detail than the home page.
Click on the document icon for the full details on any report.</p>

<h3>Export CSV</h3>

<p>Export the existing report data in comma separated format.</p>

<h3>Categories</h3>

<p>The categories are the different services that are offered by the endpoint. These are exposed
in the service discovery request of Open311. In terms of FixMyStreet, these may appear in the
drop-down menu of problem creation. For this reason don't change these values casullay, if you know the
if the FMS client is polling it for service discovery.</p>

<h3>Statuses</h3>

<p>The statuses must coincide with statuses that the FMS client is using. </p>

<p>Status changes may be pushed back to the FMS client if the systems are both configured to behave in
this way. Furthermore, if FMS is running with the Message Manager, status changes may be transmitted
back to the original problem reporter by SMS. </p>

<h3>Settings</h3>

<p>Use the Settings page to configure the endpoint.</p>

<h3>API keys</h3>

<p>The API keys are used to track from which clients the endpoint will accept updates. Note that you must
create clients before you can allocate API keys to them.</p>

<h3>Clients</h3>

<p>Use the clients page to configure and inspect the Open311 clients that can submit reports to the endpoint.</p>

<p>The client URL is used to create links back to the client website. The string <code>%id%</code> will be replaced with
the unique report ID if the client has such URLs. For example, for FixMyStreet, use
<code>http://www.fixmystreet.com/report/%id%</code></p>

<h3>Users</h3>

<p>Users have different privleges based on which group they belong. Only grant admin privilege to users who
you know will need it. Only admin users can create other users or edit statuses, API keys, settings, and
clients.</p>

<p>An admin can suspend an existing user by toggling the status, which is <em>active</em> by default.</p>

<h3>Logout</h3>

<p>Be sure to end your session by clicking on Logout, or quitting the browser (closing the window is not enough).</p>

</div>