# code sample symfony

This is a piece of functionality I integrated in a project I worked on.

This code is not working as is, it was extracted from the main app and anonymized, to avoid NDA infringement.

### What it does:

After the workflow of the main app is completed, the final step is to send contract data and customer data to an external service (Microsoft Navision) via SOAP api


The workflow is as follwing:
* Using Chain of Responsibility a flow is defined (`\config\flows.yaml`) and it's triggered by the main app
when certain conditions are meet.
* The last step in the flow (`flow.request.upload-contract`) is `NavHandler::sendContractToNav()`
* Handlers are used in this application to communicate with external services
* In the sendContractToNav function bedise the main object data container `$creditRequest`, 
another 4 params are passed as they came from another microservices and don't need to polute the main object.
* Data is transformed and passed to the external Navision service.
