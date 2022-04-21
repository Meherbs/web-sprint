<?php

namespace App\Controller;

use Twilio\Rest\Client;
use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $userPasswordEncoder,
         \Swift_Mailer $mailer, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();
            
            // sending email 
            $email = (new \Swift_Message('subscription Email'))
                ->setFrom('mailersendj1@gmail.com')
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView(
                        // templates/emails/registration.html.twig
                        'emails/registration.html.twig',
                        ['username' => $user->getUsername()]
                    ),
                    'text/html'
                );

            $mailer->send($email);

            $sid    = "AC0590a507c82a4dedf7e2b59eeb81baf7";
            $token  = "03a962dc2346d5266946e3de591776f8";
            $twilio = new Client($sid, $token);
            $test = $user->getPhone();

            // sending message sms    
            $message = $twilio->messages
                ->create(
                    $test, // to 
                    array(
                        "messagingServiceSid" => "MG6bab091d610907bb2021edc85f426141",
                        "body" => " You have succeffully registered to our platform"
                    )
                );

            return $this->redirectToRoute('app_login');
        }
        
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView()
        ]);
    }
}
