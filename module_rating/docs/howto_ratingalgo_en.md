#HowTo: Rating-Algorithms

This document  describes the way to implement your own rating-algorithm for Kajona >= 3.1.9 as used by the module “Ratings”. This module provides users the possibility to rate records by assigning points or stars to them.


The more stars a record gets, the higher it should be ranked. Since those values can be based on different mathematical facts, the module provides interfaces to calculate those values with different algorithms.
All data needed therefore is collected in the background and can be loaded during the generation of those values.

As a (rather simple) example, we will step through the absolute-algorithm packaged with the standard-installation.

Before looking at the code, a few background-information about the data available and how it is aggregated within the system.
If a user rates an item, two steps are performed at the backend.

At first, the algorithm to be created is called in order to calculate the new rating-value for the record.

In a second step, a history-entry is created containing the id of user who rated (or an empty string in case of a guest), the systemid which was rated and the rating the user sent for the record.
This means, that the history provided to the algorithm does not yet contain the current rating – it will be appended afterwards.

If you want to implement your own algorithm, you have to create a new class implementing the interface interface_module_rating_algo. This interface contains only one method:

```
<?php
interface interface_module_rating_algo {
  public function doRating(class_module_rating_rate $objSourceRate, $floatNewRating);
}
```


Lets have a look at the different parameters and the semantic meaning of the method.
The method is called within the method ``saveRating()`` of the class ``class_module_rating_rate``. saveRating() is used by the framework to make the rating, passed by the user, persistent to the database. Therefore ``saveRating()`` creates an instance of the rating-algorithm and invokes doRating() in order to get the new, recalculated rating for the current record.
To provide all needed data, doRating() contains two parameters in its signature:
``$objSourceRate``, the current(!) instance of class_module_rating_rate and
``$floatNewRating,`` the rating passed to the system by the user.
Within your rating-algorithm you can now use all the methods provided by the class ``class_module_rating_rate`` to calculate the new value.
As an example, we'll have a look at a rather simple algorithm, calculating the value using the absolute average.

```
<?php
class class_modul_rating_algo_absolute implements interface_modul_rating_algo {	
 public function doRating(class_module_rating_rate $objSourceRate, $floatNewRating) {

  $floatNewRating = (($objSourceRate->getFloatRating() * 
                      $objSourceRate->getIntHits()) + $floatNewRating) / 
                     ($objSourceRate->getIntHits()+1);
        
  $floatNewRating = round($floatNewRating, 2);
  return $floatNewRating;

 }	
}
```

The mathematical base of this algorithm should not matter, since it's nothing special. It only takes the number of votes already existing for the record and updates the rating.
If you want to implement a more complex algorithm, you may need the history of rating in order to loop over the single ratings or to exclude ratings older than 2 years.

In this case, you can load the history using ``$objSourceRate->getRatingHistoryAsArray()``. This method loads the history up till now (so NOT including the current rating!!) from the database and returns it within an array. This array contains the keys:

* ``rating_history_id`` --> used internally  
* ``rating_history_rating`` --> the current rating-systemid
* ``rating_history_user`` --> the systemid if the user who rated or '' in case of a guest
* ``rating_history_timestamp`` --> timestamp of the rating   
* ``rating_history_value`` --> the value the user rated the record

With this data you should be able to generate the value the way you want.
As a last step, please return the value calculated by you algorithm. The returned value is saved back to the database.
After saving this value, the current rating is appended to the history.

If you want to use your algorithm, change the object-creation in the method saveRating() of the class class_module_rating_rate. The method should contain two lines like

```
$objRatingAlgo = new class_modul_rating_algo_absolute();
```

Change them to match your newly created class and have a look at the ratings.

Note: If you change the class, the values are note recalculated on the fly. You have to fire new ratings or delete the ratings already existing.