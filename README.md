
### Notes
* I have updated the symphony crawler to 7.0 since I was running php8.3 rather than 7.4
* I have used de-dupe logic at the end eliminating them only if they are identical in all aspects( I thought of dedeuping using title and color but what if the site had another link which was by a different seller so listed differently)
* I have looped through colors to treat each color as a new item
* Pagination is accounted for to get all the products.
* shipping date the number of varitions the site has for this made me write lot of regex to accomdate for all scenarios.
* I have used Out of stock as the conditon to set isavailable anything other than out of stock/null will give me an true.




### Requirements

* PHP 8.0+
* Composer

### Setup

```
git clone https://github.com/kiragawd/Magpie-coding-challenge.git
cd Magpie-coding-challenge
composer install
```

To run the scrape you can use `php src/Scrape.php`
