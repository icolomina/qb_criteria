### Installation

Install this component using composer:

```shell
composer require ict/qb_criteria:dev-master
```

### How to use it

Imagine you have the following class:

```php
class ListContractsInput
{
    #[Assert\DateTime(message: 'Start at must be a valid datetime')]
    private ?string $startAt = null;

    #[Assert\DateTime(message: 'End at must be a valid datetime')]
    private ?string $endAt = null;

    public function getStartAt(): ?string
    {
        return $this->startAt;
    }

    public function setStartAt(?string $startAt): void
    {
        $this->startAt = $startAt;
    }

    public function getEndAt(): ?string
    {
        return $this->endAt;
    }

    public function setEndAt(?string $endAt): void
    {
        $this->endAt = $endAt;
    }
}
```

And you want to use its data as a filter for field _createdAt_ in your query. First of all you have to create a class which extends from 
_Ict\QbCriteria\QueryBuilderCriteriaManager_ and create a method for each value you want to filter. 

```php
class ContractsCriteriaManager extends QueryBuilderCriteriaManager
{
    /**
     * @throws \Exception
     */
    public function getStartAtCriteria(QueryBuilder $qb, string $alias, string|\DateTimeImmutable $value): void
    {
        $qb
            ->andWhere($qb->expr()->gte("{$alias}.createdAt",':start_at'))
            ->setParameter('start_at', $this->getAsDateTime($value))
        ;
    }

    /**
     * @throws \Exception
     */
    public function getEndAtCriteria(QueryBuilder $qb, string $alias, string|\DateTimeImmutable $value): void
    {
        $qb
            ->andWhere($qb->expr()->gte("{$alias}.createdAt",':end_at'))
            ->setParameter('end_at', $this->getAsDateTime($value))
        ;
    }
}
```
The last class contains to methods: 

- _getStartAtCriteria_: It adds a filter that ensures createdAt is greater than startAt value
- _getEndAtCriteria_: It adds a filter that ensures createdAt is less than endAt value

> It's mandatory to format method names as get{KeyName}Criteria. Otherwise, base class will not find them.

### Use in your repository methods

```php
 public function getList(array|object $criteria, ?User $user, ?int $limit): array
 {
     $criteriaManager = new ContractsCriteriaManager();
     $qb = $this->createQueryBuilder(self::ALIAS);

     if($limit){
        $qb->setMaxResults($limit);
     }

     $qb->orderBy(self::ALIAS . '.createdAt', 'desc');
     $criteriaManager->addCriteria($qb, self::ALIAS, $criteria);
     return $qb->getQuery()->getResult();
 }
```

As you can see at the end of the method, you pass your query builder, your alias and criteria, and it will fill it
with the right filters.

You can pass an array instead of an object: For instance, following the above model, you would pass an array like this:

```php
[
   "startAt" : "2023-05-03 16:45:00",
   "end_at" : "2023-05-11 16:45:00"
]
```

