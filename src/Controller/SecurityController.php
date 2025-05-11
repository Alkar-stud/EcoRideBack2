<?php

namespace App\Controller;

use App\Entity\EcoRide;
use App\Entity\User;
use App\Factory\UserFactory;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use GdImage;
use Nelmio\ApiDocBundle\Attribute\Areas;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\MediaType;
use OpenApi\Attributes\Schema;
use Nelmio\ApiDocBundle\Attribute\Model;
use Random\RandomException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;


#[Route('/api', name: 'app_api_')]
#[OA\Tag(name: 'User')]
#[Areas(["default"])]
final class SecurityController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface      $manager,
        private readonly SerializerInterface         $serializer,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly UserFactory $userFactory,
    )
    {
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/registration', name: 'registration', methods: 'POST')]
    #[OA\Post(
        path:"/api/registration",
        summary:"Inscription d'un nouvel utilisateur",
        requestBody :new RequestBody(
            description: "Données de l'utilisateur à inscrire",
            required: true,
            content: [new MediaType(mediaType:"application/json",
                schema: new Schema(properties: [new Property(
                    property: "pseudo",
                    type: "string",
                    example: "Pseudo"
                ),
                    new Property(
                        property: "email",
                        type: "string",
                        example: "adresse@email.com"
                    ),
                    new Property(
                        property: "password",
                        type: "string",
                        example: "M0t de passe"
                    )], type: "object"))]
        ),
    )]
    #[OA\Response(
        response: 201,
        description: 'Utilisateur inscrit avec succès',
        content: new Model(type: User::class, groups: ['user_login'])
    )]
    #[OA\Response(
        response: 400,
        description: 'Le mot de passe doit contenir au moins 10 caractères, une lettre majuscule, une lettre minuscule, un chiffre et un caractère spécial. Ou il manque le pseudo'
    )]
    #[OA\Response(
        response: 409,
        description: 'Ce compte existe déjà'
    )]
    public function register(MailerInterface $mailer, Request $request, UserPasswordHasherInterface $passwordHasher, VerifyEmailHelperInterface $verifyEmailHelper): JsonResponse
    {
        $user = $this->serializer->deserialize($request->getContent(), User::class, 'json');

        //Vérification que les champs sont tous renseignés
        if (null === $user->getPseudo() || null === $user->getPassword() || null === $user->getEmail()) {
            return new JsonResponse(['error' => true, 'message' => 'Informations incomplètes'], Response::HTTP_BAD_REQUEST);
        }

        //Vérification de l'existence de l'utilisateur pour ne pas avoir d'email en double
        $existingUser = $this->manager->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
        if ($existingUser) {
            return new JsonResponse(['error' => true, 'message' => 'Ce compte existe déjà'], Response::HTTP_CONFLICT);
        }

        //Validation de la complexité du mot de passe
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{10,}$/', $user->getPassword())) {
            return new JsonResponse(['message' => 'Le mot de passe doit contenir au moins 10 caractères, une lettre majuscule, une lettre minuscule, un chiffre et un caractère spécial.'], Response::HTTP_BAD_REQUEST);
        }

        // Création de l'utilisateur via la factory
        $user = $this->userFactory->createUser(
            $user->getEmail(),
            $user->getPseudo(),
            $passwordHasher->hashPassword(new User(), $user->getPassword())
        );

        // Recherche de l'entité EcoRide avec le libelle "WELCOME_CREDIT"
        $ecoRide = $this->manager->getRepository(EcoRide::class)->findOneBy(['libelle' => 'WELCOME_CREDIT']);
        // Vérification si l'entité existe et récupération de la valeur des crédits
        $welcomeCredit = $ecoRide ? (int) $ecoRide->getParameters() : 0;
        // Attribution des crédits à l'utilisateur
        $user->setCredits($welcomeCredit);

        $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));

        $this->manager->persist($user);
        $this->manager->flush();

        //Envoie du mail de vérification
        //Le tableau ['id' => $user->getId()] est pour inclure l'identifiant utilisateur,
        //car le user n'est pas connecté lorsqu'il clique sur le lien puisqu'il ne peut pas se connecter tant qu'il n'est pas vérifié
        $signatureComponents = $verifyEmailHelper->generateSignature(
            'app_api_verify_email',
            $user->getId(),
            $user->getEmail(),
            ['id' => $user->getId()]
        );

        $email = (new Email())
            ->from('ecorideback@alwaysdata.net')
            ->to($user->getEmail())
            ->subject('Bienvenue chez EcoRide !')
            ->text('Bonjour, ' . $user->getPseudo(). ', bienvenue chez nous !, veuillez copier/coller le lien suivant pour valider votre adresse email: '.$signatureComponents->getSignedUrl())
            ->html('Bonjour, ' . $user->getPseudo(). ', bienvenue chez nous !, <br>Veuillez <a href="'.$signatureComponents->getSignedUrl().'">cliquer ic</a> pour valider votre adresse email');

        $mailer->send($email);

        $responseData = $this->serializer->serialize(
            $user,
            'json',
            ['groups' => ['user_login']]
        );

        return new JsonResponse($responseData, Response::HTTP_CREATED, [], true);
    }

    #[Route('/verify', name: 'verify_email', methods: 'GET')]
    public function verifyUserEmail(Request $request, VerifyEmailHelperInterface $verifyEmailHelper, UserRepository $userRepository): JsonResponse|RedirectResponse
    {
        $user = $userRepository->find($request->query->get('id'));
        if (!$user) {
            return new JsonResponse(['error' => true, 'message' => 'Email inconnu'], Response::HTTP_BAD_REQUEST);
        }
        try {
            $verifyEmailHelper->validateEmailConfirmation(
                $request->getUri(),
                $user->getId(),
                $user->getEmail(),
            );
        } catch (VerifyEmailExceptionInterface $e) {
            return new JsonResponse(['error' => true, 'message' => $e->getReason()], Response::HTTP_BAD_REQUEST);
        }

        $user->setIsVerified(true);
        $this->manager->flush();

        $responseData = $this->serializer->serialize(
            $user,
            'json',
            ['groups' => ['user_login']]
        );

        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }



    #[Route('/login', name: 'login', methods: 'POST')]
    #[OA\Post(
        path:"/api/login",
        summary:"Connecter un utilisateur",
        requestBody :new RequestBody(
            description: "Données de l’utilisateur pour se connecter",
            required: true,
            content: [new MediaType(mediaType:"application/json",
                schema: new Schema(properties: [new Property(
                    property: "email",
                    type: "string",
                    example: "adresse@email.com"
                ),
                    new Property(
                        property: "password",
                        type: "string",
                        example: "Mot de passe"
                    )], type: "object"))]
        ),
    )]
    #[OA\Response(
        response: 200,
        description: "Connexion réussie",
        content: new Model(type: User::class, groups: ['user_login'])
    )]
    public function login(#[CurrentUser] ?User $user): JsonResponse | RedirectResponse
    {
        if (null === $user) {
            return new JsonResponse(['error' => true, 'message' => 'Missing credentials'], Response::HTTP_UNAUTHORIZED);
        }
        //Si le user n'a pas le droit de se connecter
        if (!$user->isActive()) {
            return new JsonResponse(['error' => true, 'message' => 'Votre compte est désactivé. Veuillez nous contacter pour plus d\'informations'], Response::HTTP_FORBIDDEN);
        }

        //Si le user n'a pas validé son email
        if (!$user->isVerified()) {
            return new JsonResponse(['error' => true, 'message' => 'Vous devez valider votre email avant de pouvoir vous connecter'], Response::HTTP_FORBIDDEN);
        }

        $responseData = $this->serializer->serialize(
            $user,
            'json',
            ['groups' => ['user_login']]
        );

        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }

    #[Route('/account/me', name: 'account_me', methods: 'GET')]
    #[OA\Get(
        path:"/api/account/me",
        summary:"Récupérer toutes les informations du User connecté",
    )]
    #[OA\Response(
        response: 200,
        description: 'User trouvé avec succès',
        content: new Model(type: User::class, groups: ['user_read', 'vehicle_read'])
    )]
    #[OA\Response(
        response: 404,
        description: 'User non trouvé'
    )]
    public function me(#[CurrentUser] ?User $user): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        if (null === $user) {
            return new JsonResponse(['error' => true, 'message' => 'Missing credentials'], Response::HTTP_UNAUTHORIZED);
        }

        $responseData = $this->serializer->serialize(
            $user,
            'json',
            ['groups' => ['user_read', 'vehicle_read']]
        );

        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }

    #[Route('/account/edit', name: 'account_edit', methods: 'PUT')]
    #[OA\Put(
        path:"/api/account/edit",
        summary:"Modifier son compte utilisateur",
        requestBody :new RequestBody(
            description: "Données de l'utilisateur à modifier",
            content: [new MediaType(mediaType:"application/json",
                schema: new Schema(properties: [new Property(
                    property: "pseudo",
                    type: "string",
                    example: "Nouveau pseudo"
                ),
                    new Property(
                        property: "photo",
                        type: "string",
                        example: "Nouvelle photo (jpg, png ou webp, max 100px*100px)"
                    ),
                    new Property(
                        property: "isDriver",
                        type: "boolean",
                        example: true
                    ),
                    new Property(
                        property: "isPassenger",
                        type: "boolean",
                        example: true
                    ),
                ], type: "object"))]
        ),
    )]
    #[OA\Response(
        response: 200,
        description: 'User modifié avec succès',
        content: new Model(type: User::class, groups: ['user_read'])
    )]
    #[OA\Response(
        response: 400,
        description: 'Erreur dans l\'envoi des données'
    )]
    #[OA\Response(
        response: 404,
        description: 'User non trouvé'
    )]
    public function edit(
        #[CurrentUser] ?User $user,
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        #[Autowire('%kernel.project_dir%/public/uploads/photos')] string $photoDirectory
    ): JsonResponse
    {
        if (null === $user) {
            return new JsonResponse(null, Response::HTTP_UNAUTHORIZED);
        }

        $originalEmail = $user->getEmail();
        $originalRoles = $user->getRoles();
        $originalIsActive = $user->isActive();
        $originalCredits = $user->getCredits();

        $user = $this->serializer->deserialize(
            $request->getContent(),
            User::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $user]
        );
        // Empêcher la modification des rôles par le User
        $user->setRoles($originalRoles);
        // Empêcher la modification de isActive par le User
        $user->setIsActive($originalIsActive);
        // Empêcher la modification de l'email par le User
        $user->setEmail($originalEmail);

        //Si deletePhoto === true, on supprime le fichier $user->getPhoto() dans uploads/photo et $user->setPhoto() = null;
        // Récupération des données de la requête.
        $data = json_decode($request->getContent(), true);

        // Vérification si deletePhoto est présent dans la requête et est true
        if (isset($data['deletePhoto']) && $data['deletePhoto'] === true) {
            $oldPhotoPath = $photoDirectory . '/' . $user->getPhoto();
            if ($user->getPhoto() && file_exists($oldPhotoPath) && is_writable($oldPhotoPath)) {
                @unlink($oldPhotoPath);
                $user->setPhoto(null);
            }
        }

        if (isset($request->toArray()['password'])) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPassword()));
        }

        $user->setUpdatedAt(new DateTimeImmutable());

        // Vérifier si l'utilisateur a le rôle ROLE_ADD_CREDIT avant de modifier les crédits
        if (!$this->isGranted('ROLE_ADD_CREDIT')) {
            $user->setCredits($originalCredits);
        }
        //Vérification de l'intégrité de l'upload de la photo et on lui met comme nom un slug.


        $user->setUpdatedAt(new DateTimeImmutable());

        $this->manager->flush();

        $responseData = $this->serializer->serialize(
            $user,
            'json',
            ['groups' => ['user_read']]
        );

        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }

    /**
     * @throws RandomException
     */
    #[Route('/account/upload', name: 'account_upload', methods: 'POST')]
    #[OA\Post(
        path:"/api/account/upload",
        summary:"Envoyer la photo de profil",
        requestBody :new RequestBody(
            description: "Données de l'utilisateur à modifier",
            content: [new MediaType(mediaType:"application/json",
                schema: new Schema(properties: [new Property(
                    property: "photo",
                    type: "file",
                    example: "image.jpg"
                ),
                ], type: "object"))]
        ),
    )]
    #[OA\Response(
        response: 200,
        description: 'Image envoyé avec succès',
        content: new Model(type: User::class, groups: ['user_read'])
    )]
    #[OA\Response(
        response: 400,
        description: 'Erreur dans l\'envoi des données'
    )]
    #[OA\Response(
        response: 404,
        description: 'User non trouvé'
    )]
    public function upload(
        #[CurrentUser] ?User $user,
        Request $request,
        #[Autowire('%kernel.project_dir%/public/uploads/photos')] string $photoDirectory
    ): JsonResponse {
        if ($user === null) {
            return new JsonResponse(['error' => true, 'message' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }
        if (!$request->files->has('photo')) {
            return new JsonResponse(['error' => true, 'message' => 'Aucun fichier photo trouvé'], Response::HTTP_BAD_REQUEST);
        }

        $photoFile = $request->files->get('photo');
        $newFilename = bin2hex(random_bytes(16)) . '.' . $photoFile->guessExtension();

        // Vérification de l'extension
        $allowedExtensions = ["jpeg", "jpg", "png", "webp"];
        if (!in_array($photoFile->guessExtension(), $allowedExtensions)) {
            return new JsonResponse(['error' => true, 'message' => 'L\'extension de la photo est incorrecte'], Response::HTTP_BAD_REQUEST);
        }

        // Vérification de la taille
        if ($photoFile->getSize() > 2000000) {
            return new JsonResponse(['error' => true, 'message' => 'La photo est trop lourde'], Response::HTTP_BAD_REQUEST);
        }

        // Vérification des dimensions
        $image = getimagesize($photoFile->getPathname());
        if ($image[0] > 100 || $image[1] > 100) {
            try {
                // On redimensionne la photo
                $resizedImage = $this->resizeAvatar($photoFile);
                $targetPath = $photoDirectory . '/' . $newFilename;

                // Enregistrer l'image redimensionnée
                switch ($photoFile->getMimeType()) {
                    case 'image/jpeg':
                        if (!@imagejpeg($resizedImage, $targetPath)) {
                            throw new \Exception("Impossible d'écrire l'image JPEG");
                        }
                        break;
                    case 'image/png':
                        if (!@imagepng($resizedImage, $targetPath)) {
                            throw new \Exception("Impossible d'écrire l'image PNG");
                        }
                        break;
                    case 'image/webp':
                        if (!@imagewebp($resizedImage, $targetPath)) {
                            throw new \Exception("Impossible d'écrire l'image WebP");
                        }
                        break;
                    default:
                        throw new \Exception("Format d'image non pris en charge");
                }

                imagedestroy($resizedImage);

                if ($user->getPhoto()) {
                    $oldPhotoPath = $photoDirectory . '/' . $user->getPhoto();
                    if (file_exists($oldPhotoPath) && is_writable($oldPhotoPath)) {
                        @unlink($oldPhotoPath);
                    }
                }
                // On met à jour l'utilisateur avec le nouveau nom de fichier
                $user->setPhoto($newFilename);
                $this->manager->flush();
                return new JsonResponse(['success' => true, 'message' => 'Photo redimensionnée et uploadée avec succès'], Response::HTTP_OK);
            } catch (\Exception $e) {
                return new JsonResponse(['error' => true, 'message' => 'Erreur lors du redimensionnement de l\'image: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        // Déplacement de la nouvelle photo s'il n'y a pas de redimensionnement à faire
        try {
            $photoFile->move($photoDirectory, $newFilename);
            $user->setPhoto($newFilename);
            $this->manager->flush();
        } catch (FileException $e) {
            return new JsonResponse(['error' => true, 'message' => 'Erreur lors de l\'upload de la photo: ' . $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(['success' => true, 'message' => 'Photo uploadée avec succès'], Response::HTTP_OK);
    }



    #[Route('/account', name: 'account_delete', methods: 'DELETE')]
    #[OA\Delete(
        path:"/api/account",
        summary:"Supprimer son compte utilisateur",
    )]
    #[OA\Response(
        response: 204,
        description: 'User supprimé avec succès'
    )]
    #[OA\Response(
        response: 404,
        description: 'User non trouvé'
    )]
    public function delete(#[CurrentUser] ?User $user): JsonResponse
    {
        if ($user) {
            //On supprime le fichier de l'image de profil s'il y en a une
            if ($user->getPhoto()) {
                $photoDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads/photos';
                $oldPhotoPath = $photoDirectory . '/' . $user->getPhoto();
                if (file_exists($oldPhotoPath) && is_writable($oldPhotoPath)) {
                    @unlink($oldPhotoPath);
                }
            }

            // On supprime l'utilisateur
            $this->manager->remove($user);
            $this->manager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    private function resizeAvatar(mixed $photoFile): GdImage
    {
        // Vérification des dimensions de l'image
        $imageInfo = getimagesize($photoFile->getPathname());
        $originalWidth = $imageInfo[0];
        $originalHeight = $imageInfo[1];

        // Calcul des nouvelles dimensions en gardant le ratio
        $newHeight = 100;
        $newWidth = intval(($originalWidth / $originalHeight) * $newHeight);

        // Chargement de l'image source
        $source = match ($photoFile->getMimeType()) {
            'image/jpeg' => imagecreatefromjpeg($photoFile->getPathname()),
            'image/png' => imagecreatefrompng($photoFile->getPathname()),
            'image/webp' => imagecreatefromwebp($photoFile->getPathname()),
            default => throw new \InvalidArgumentException('Format d\'image non supporté'),
        };

        // Création de l'image redimensionnée
        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

        // Préservation de la transparence pour les PNG
        if ($photoFile->getMimeType() === 'image/png') {
            imagealphablending($resizedImage, false);
            imagesavealpha($resizedImage, true);
        }

        imagecopyresampled($resizedImage, $source, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

        // Libération de la mémoire de l'image source
        imagedestroy($source);

        return $resizedImage;
    }

}
