# Sadded Bahrain  Invoice

Project provides methods to generate sadded invoice and a method to check status of an already generated invoice.

Invoice can be generated using three different notification types i.e. Email, Sms and Online.

## Getting Started

### How to use

1. Just run php artisan serve  then  http://localhost:8000/invoice

2. Fill form and will make payment and return response with status 

#### Guide to Generate invoice

Pass these variables as parameters into constructor:

* URL
* Branch Id
* Vendor Id
* Terminal Id
* Api Key


Set these attributes of invoice to generate a new invoice successfully
