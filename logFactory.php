<?php

namespace Oz\Component\Log;

// TODO : Here we got a 'Couplage Fort' between log entity and the component
// Try to send the ORM Log as a parameter in the construct class or create the entity class here in the component....
use Oz\ApiBundle\Entity\Log as Log;
use Doctrine\ORM\EntityManager;

/**
 * Class LogFactory
 * @package Component\Log
 */
class LogFactory
{
    public  $response = '';
    private $type = 'tech';
    private $params = [];
    private $em;
    private $log;

    /**
     * Build up My Factory
     * My factory will always return a class object
     * define a factory as a service in Symfony ?? hard to find the way
     *
     * @param Object $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
      $this->em = $entityManager;
      $this->log = new Log();
    }


    /**
     * this is like a remmber me : we should separate func from tech logs the day when monitoring will be open
     *
     * @param string $type
     */
    public function setLogType($type)
    {
        $this->type = ($type == 'func') ? $type : 'tech';
        return $this;
    }


    /**
     * before saving , need to handle params validation based on my log table fields
     *
     * @param array $params
     */ 
    public function validParams($params)
    {
        // listing my Table fields
        $logFields = $this->em->getClassMetadata('Oz\ApiBundle\Entity\Log')->getFieldNames();

        // preparing my params : we take only correct Log fields from
        // sent params to the Factory.
        $this->params = array_intersect_key($params, array_flip($logFields));

        // adding a new field in the database type : tech or func
        //$this->type = $this->params;
        return $this;
    }

    // the final method to call : response is a status here not a json object.
    // only need to know if my logs were written or not.
    public function saveLog()
    {
        // now loop over the properties of each post array...
        foreach ($this->params as $property => $value) {
            // create a setter
            $method = sprintf('set%s', ucwords($property));
            $this->log->$method($value);
        }

        try {
            // saving my new Log
            $this->em->persist($this->log);
            $this->em->flush();
            return true;
        } catch(Exception $e) {
            // TODO : we should manage this exception somedays
            return false;
        }
        
    }
}