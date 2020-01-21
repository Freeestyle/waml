<?php

namespace App\Repository;


use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    /**
     * @param array $filters
     * @return Post[]
     */
    public function search(array $filters = [])
    {
        //methode pour traiter les données recues par le form de recherche

        $formrequete = $this->createQueryBuilder('p');
        //tri par date de publication décroissante
        $formrequete->orderBy('p.publicationDate', 'DESC');

        if (!empty($filters['title'])) {
            $formrequete
                // rajoute des element (vs ->where qui remplace completement la condition)
                ->andWhere('p.title LIKE :title')
                ->setParameter('title', '%' . $filters['title'] . '%');
        }
        if (!empty($filters['category'])) {
            $formrequete
                ->andWhere('p.category = :category')
                ->setParameter('category', $filters['category']);
        }
        if (!empty($filters['region'])) {
            $formrequete
                ->andWhere('p.region = :region')
                ->setParameter('region', $filters['region']);
        }

        $formrequete->orderBy('p.publicationDate', $filters['sortPublicationDate']);

//        echo $formrequete->getQuery()->getSQL();
        // execute la requete et on retourne le resultat
        // qui est un tableau d'obj Article comme find() ou findBy()
        return $formrequete->getQuery()->getResult();

    }

    // ***************************************************************************************
    //            METHODE native pour affichage aléatoire de 3 annonces dans la homepage
    //****************************************************************************************
    public function findRandom(int $nb = 3)
    {
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata(Post::class, 'post');

        $sql = 'select * from post order by rand() limit ' . $nb;
        return $this->getEntityManager()->createNativeQuery($sql, $rsm)->getResult();
    }

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }
}



    // /**
    //  * @return Post[] Returns an array of Post objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Post
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
