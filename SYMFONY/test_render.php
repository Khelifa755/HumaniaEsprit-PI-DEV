<?php
require 'vendor/autoload.php';
$kernel = new App\Kernel('dev', true);
$kernel->boot();
$c = $kernel->getContainer();
$twig = $c->get('twig');
$em = $c->get('doctrine')->getManager();
$reunion = $em->getRepository(\App\Entity\Reunion::class)->findOneBy([]);
if ($reunion) {
    $formFactory = $c->get('form.factory');
    $form = $formFactory->create(\App\Form\PLANNIFICATION\ReunionModalType::class, $reunion);
    echo 'OK: ' . strlen($twig->render('PLANNIFICATION/reunion/_modal_edit_reunion.html.twig', [
        'form' => $form->createView(),
        'reunion' => $reunion
    ]));
} else {
    echo 'No Reunion found';
}
