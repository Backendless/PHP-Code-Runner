<?php
return [
    
        'beforeCreate'      =>  [ 'id' => 100, 'index' => 1, 'type' => 'BEFORE' ],
        'afterCreate'       =>  [ 'id' => 101, 'index' => 1, 'type' => 'AFTER' ],
    
        'beforeFindById'   =>  [ 'id' => 102, 'index' => null, 'type' => 'BEFORE' ],
        'afterFindById'    =>  [ 'id' => 103, 'index' => null, 'type' => 'AFTER' ],

        'beforeLoadRelations'   =>  [ 'id' => 104, 'index' => null, 'type' => 'BEFORE' ],
        'afterLoadRelations'    =>  [ 'id' => 105, 'index' => null, 'type' => 'AFTER' ],
    
        'beforeRemove'   =>  [ 'id' => 106, 'index' => null, 'type' => 'BEFORE' ],
        'afterRemove'    =>  [ 'id' => 107, 'index' => null, 'type' => 'AFTER' ],

        'beforeUpdate'   =>  [ 'id' => 108, 'index' => 1, 'type' => 'BEFORE' ],
        'afterUpdate'    =>  [ 'id' => 109, 'index' => 1, 'type' => 'AFTER' ],
    
        'beforeDescribe'   =>  [ 'id' => 110, 'index' => null, 'type' => 'BEFORE' ],
        'afterDescribe'    =>  [ 'id' => 111, 'index' => null, 'type' => 'AFTER' ],
    
        'beforeFind'   =>  [ 'id' => 112, 'index' => null, 'type' => 'BEFORE' ],
        'afterFind'    =>  [ 'id' => 113, 'index' => null, 'type' => 'AFTER' ],        
    
        'beforeFirst'   =>  [ 'id' => 114, 'index' => null, 'type' => 'BEFORE' ],
        'afterFirst'    =>  [ 'id' => 115, 'index' => null, 'type' => 'AFTER' ],        
        
        'beforeLast'   =>  [ 'id' => 116, 'index' => null, 'type' => 'BEFORE' ],
        'afterLast'    =>  [ 'id' => 117, 'index' => null, 'type' => 'AFTER' ],        
    
        'beforeUpdateBulk'   =>  [ 'id' => 118, 'index' => null, 'type' => 'BEFORE' ],
        'afterUpdateBulk'    =>  [ 'id' => 119, 'index' => null, 'type' => 'AFTER' ],        
    
        'beforeRemoveBulk'   =>  [ 'id' => 120, 'index' => null, 'type' => 'BEFORE' ],
        'afterRemoveBulk'    =>  [ 'id' => 121, 'index' => null, 'type' => 'AFTER' ],        
    
       ];
