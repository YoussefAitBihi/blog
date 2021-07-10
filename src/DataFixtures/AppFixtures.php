<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Post;
use App\Entity\User;
use App\Entity\Image;
use App\Entity\Comment;
use App\Entity\Role;
use App\Entity\PostLike;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    /**
     * L'encodeur du mot de passe
     *
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * Permet de recupérer l'encodeur via l'injection de dépendance
     *
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(UserPasswordEncoderInterface $encoder) 
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager) 
    {

        define('P_D', dirname(dirname(__DIR__)));
        
        $faker = Factory::create();

        // Supprimer toutes les photos de profils

        $pictures = scandir(P_D . "/public/uploads/pictures");

        if (!$pictures) {
            dd($pictures);
            foreach ($pictures as $picture) {
                if (is_file(P_D . "/public/uploads/pictures" . DIRECTORY_SEPARATOR . $picture)) {
                    unlink(P_D . "/public/uploads/pictures" . DIRECTORY_SEPARATOR . $picture);
                }
            }
        }


        // Tous les utilisateurs
        $users = array();
        // Création de 10 utilisateurs
        for ($i = 0; $i < 10; $i++):
            // Nouveau utilisateur
            $user = new User();

            // Mot de passe encoded
            $passwordEncoded = $this->encoder->encodePassword($user, 'motdepasse123');
            // La description
            $description = implode('.', $faker->paragraphs(mt_rand(10, 15), false));
            // Picture
            $pic = $faker->file(
                P_D . '/tmp_avatars',
                P_D . '/public/uploads/picture', 
                false
            );
            
            $user
                ->setFirstName($faker->firstName)
                ->setLastName($faker->lastName)
                ->setEmail($faker->email)
                ->setHash($passwordEncoded)
                ->setDescription($description)
                ->setPicture($pic);

            // Enregitrement de l'objet $user
            $manager->persist($user);

            // Ajout de l'objet $user
            array_push($users, $user);

        endfor; // End User

        // L'ajout de rôle Admin
        $role = new Role();

        $role
            ->setName('ROLE_ADMIN')
            ->addUser($users[mt_rand(0, count($users) - 1)]);
        
        $manager->persist($role);

        // Création de 20 postes
        for ($p = 0; $p < 20; $p++):
            // Nouveau poste
            $post = new Post();

            // La description
            $description = implode(',', $faker->paragraphs(mt_rand(12, 20)));

            $post
                ->setTitle($faker->sentence(mt_rand(3, 6), true))
                ->setIntrodution($faker->sentence(mt_rand(10, 15)))
                ->setDescription($description)
                ->setImage($faker->file(
                    P_D . '/tmp_thumbnails', 
                    P_D . '/public/uploads/picture', 
                    false
                ))
                ->setVideo($faker->file(
                    P_D . '/tmp_videos',
                    P_D . '/public/uploads/videos', 
                    false
                ))
                ->setAuthor($users[mt_rand(0, count($users) - 1)]);

            $manager->persist($post);

            // Création entre 2 et 4 images
            for ($i = 0; $i < mt_rand(2, 4); $i++):
                // Nouvelle image
                $image = new Image();

                $image
                    ->setCaption($faker->sentence())
                    ->setImg($faker->file(
                        P_D . '/tmp_thumbnails',
                        P_D . '/public/uploads/carousel',
                        false
                    ))
                    ->setPost($post);

                $manager->persist($image);

            endfor; // End Image

            // Création entre 3 et 5 commentaires
            for ($c = 0; $c < mt_rand(3, 5); $c++):

                // Nouveau commentaire
                $comment = new Comment();

                // Le commentateur
                $commentator = $users[mt_rand(0, count($users) - 1)];
                // le créateur de poste
                $poster = $post->getAuthor();

                // Si le créateur du poste étant le commentateur 
                while ($commentator === $poster):
                    $commentator = $users[mt_rand(0, count($users) - 1)];
                endwhile;

                $comment
                    ->setContent($faker->sentence(mt_rand(3, 5)))
                    ->setRating(mt_rand(1, 5))
                    ->setAuthor($commentator)
                    ->setPost($post);
                
                $manager->persist($comment);

            endfor; // End Comment

            // Création entre 3 et 5 like pour chaque poste
            for ($l = 0; $l < mt_rand(3, 5); $l++):

                // Nouvelle Like
                $like = new PostLike();

                $like
                    ->setPostLike($post)
                    ->setUserLike($users[mt_rand(0, count($users) - 1)]);
                
                $manager->persist($like);

            endfor;

        endfor; // End Post
        
        // Enregistrement de l'objet $user dans la base de données
        $manager->flush();

    }

}
