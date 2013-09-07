# Lilmuckers_AsynchronousIndex

Extension for **Magento Enterprise 1.13** to plug into the default Indexing functionality.

This module is aimed at **Enterprise** users who are looking for a quick way to attempt to increase the performance of their backend interface. It achieves this by forking the processing of the *update on save* events off to an external worker running on **breanstalkd** (or a similar queueing system supported by [Lilmuckers_Queue] (https://github.com/lilmuckers/magento-lilmuckers_queue]).

This is no-where near a magic bullet solution, and should be used in conjunction with other optimisations to achieve the desired level of performance, it's merely a single bullet in the bandolier.

In testing I ran it against a database of **17000 products** and **225 store views**, and product and category save time decreased from an average of **93 seconds** to **8 seconds**. Which is a fair boost to the performance of the system.

If your site is a lot simpler (only contained a few store views and/or products) then the chances are you will not experience such a marked increase in performance.

## Requirements
 * Magento Enterprise 1.13 or higher
 * [Lilmuckers_Queue] (https://github.com/lilmuckers/magento-lilmuckers_queue) v0.2.2 or better
 
## Configuration
 * Enable the module under **System > Configuration > Index Management > Index Options**
 
## Functional Overview
This module works by overwriting the **event handlers** within **Enterprise_Catalog**, such that the event observer data is fed into the **queue** system, which then will directly run the index refresh in a seperate process.

## Status
This is **BETA** software, and not tested on any kind of production environment. Infact I built it in 4 hours or so as a proof-of-concept, and that concept appears to have been proven.
It has not been exhaustively tested, however it should theoretically be fine, but test it hugely before deploying.