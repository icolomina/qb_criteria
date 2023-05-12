<?php

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use function Symfony\Component\String\u;

abstract class QueryBuilderCriteriaManager
{
    private SerializerInterface $serializer;

    public function __construct()
    {
        $this->serializer = new Serializer([new ObjectNormalizer()]);
    }

    /**
     * @throws ExceptionInterface
     */
    public function addCriteria(QueryBuilder $qb, string $alias, iterable|object $filters): void
    {
        if(!is_object($filters)){
            foreach ($filters as $key => $value) {
                $this->addToQb($qb, $alias, $key, $value);
            }
        }
        else{
            $criteriaData = $this->serializer->normalize($filters);
            foreach ($criteriaData as $propName => $value) {
                $this->addToQb($qb, $alias, $propName, $value);
            }
        }
    }

    protected function addToQb(QueryBuilder $qb, string $alias, string $key, mixed $value): void
    {
        $method = u('get_' . $key . 'Criteria')->camel()->toString();

        if( !empty($value) || $value === 0 || $value === '0' || $value === false) {
            if(method_exists($this, $method)){
                $this->$method($qb, $alias, $value);
            }
            else{
                $qb
                    ->andWhere($qb->expr()->eq("{$alias}.{$key}", ':' . $key))
                    ->setParameter($key, $value)
                ;
            }
        }

    }

    /**
     * @throws \Exception
     */
    protected function getAsDateTime(string|\DateTimeImmutable $date): \DateTimeImmutable
    {
        return ($date instanceof \DateTimeImmutable)
            ? $date
            : new \DateTimeImmutable($date)
            ;
    }
}
