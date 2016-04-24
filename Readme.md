## Portachtzig Neos Piwik Package

#### Track visits in your Neos Site with Piwik!

This adds a Piwik panel to the Property Inspectory and shows individual statistics for the selected page.
You can edit basic settings for your Piwik site from within the Neos Backend Module.

To get it running you simply need to enter your Piwk hostname and token and your're done! The Piwik tracking code will be autmotaically generated for your page.

- - -

> Piwk - Liberating Analytics
> http://piwik.org/

- - -


#### Requirements

+ Neos CMS
+ Running Piwik Installation (http://piwik.org/docs/installation/#the-5-minute-piwik-installation)
+ https encryption (e.g. let's encrypt)
+ cURL

- - -

#### Installation

```
	$ composer install portachtzig/neos-piwik
```

### Configuration
To connect Neos with your Piwik installation you just have to enter your hostname and token_auth in the Neos Backend Module "Piwik" and select the site you cretaed in Piwik to track your user's statistics.

+ enter hostname of your Piwik installation
The host has to be reachable via https.

+ enter token_auth of a piwik admin user
You have to enter a valid auth token of an Piwik admin user.

+ select Piwik site you want to connect your Neos Site with
You need to create a Piwik Site which will track

#### Configure Piwik Site
**options**
+ name of Site
this will change the name of your Piwik site

+ main URL
the primary URL for which Piwik will track visits

+ Exclude IPs
IP addresses Piwik tracking should be disabled for.

+ Excluded Parameters
URL Query parameters

+ Site Search
Track internal search requests.

+ Time Zone
Set  the time zone for your Piwik site.

+ Currency
Set a currency for your Piwik site.

### View statistics 

![visist per day / last week](Documentation/Images/visits_per_day_week.png)


