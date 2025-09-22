<?php
namespace App\Controller;

use App\Entity\Spectator;
use App\Enum\RegistrationStatus;
use App\Repository\RegistrationRepository;
use App\Repository\RunEventRepository;
use App\Repository\SpectatorRepository;
use App\Service\RegistrationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request, RedirectResponse, Session\SessionInterface};
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractController{
    public function __construct(
        private RunEventRepository $runEventRepository,
        private RegistrationRepository $registrationRepository,
        private SpectatorRepository $spectatorRepository,
        private EntityManagerInterface $emi,
        private RegistrationService $registrationService
    ){}

    #[Route('/', name: 'event_page', methods: ['GET'])]
    public function index(Request $request, SessionInterface $sessionInterface) {
        $runEvent = $this->runEventRepository->find(1);
        if (!$runEvent) {
            throw $this->createNotFoundException('Rund event (id = 1) nem található!');
        }

        $spectator = null;
        $registrations = [];

        if ($sessionInterface->has('spectator_id')) {
            $spectator = $this->spectatorRepository->find($sessionInterface->get('spectator_id'));
            if ($spectator) {
                $registrations = $this->registrationRepository->findBy(
                    ['spectator' => $spectator, 'event' => $runEvent], ['id' => 'ASC']
                );
            }

            else {
                $sessionInterface->remove('spectator_id');
            }
        }

        $openModal = (string) $request->query->get('m', '');
        $prefillEmail = (string) $request->query->get('email', '');
        $standbyCount = $this->registrationRepository->countMembers($runEvent);
        $freeSlots = max(0, $runEvent->getCapacity() - $standbyCount);

        $waitlistPosition = null;
        if ($spectator) {
            $spectatorRegistration = $this->registrationRepository->findOneBy(['event' => $runEvent, 'spectator' => $spectator]);

            if ($spectatorRegistration && $spectatorRegistration->getStatus() === RegistrationStatus::WAITLIST) {
                $waitlistPosition = $spectatorRegistration->getWaitlistPosition();
            }
        }

        return $this->render('event/index.html.twig', [
            'event' => $runEvent, 
            'spectator' => $spectator, 
            'registrations' => $registrations,
            'openModal' => $openModal,
            'prefillEmail' => $prefillEmail,
            'freeSlots' => $freeSlots,
            'waitlistPosition' => $waitlistPosition
        ]);
    }

    #[Route('/register', name: 'event_register', methods:['POST'])]
    public function submitRegistration(Request $request, SessionInterface $sessionInterface): RedirectResponse {
        if (!$this->isCsrfTokenValid('register', (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Érvénytelen biztonsági token!');
            return $this->redirectToRoute('event_page', ['m' => 'register']);
        }

        $name = trim((string) $request->request->get('name'));
        $email = trim((string) $request->request->get('email'));
        $password = trim((string) $request->request->get('password'));

        if (!preg_match('/^[\w\.-]+@[\w.-]+\.\w{2,6}$/', $email)) {
            $this->addFlash('error', 'Érvénytelen e-mail formátum!');
            return $this->redirectToRoute('event_page', ['m' => 'register']);
        }

        $existing = $this->spectatorRepository->findOneBy(['email' => $email]);
        if ($existing) {
            $this->addFlash('error', 'Ezzel az e-maillel már van fiókod!');
            return $this->redirectToRoute('event_page', ['m' => 'login', 'email' => $email]);
        }

        $spectator = new Spectator();
        $spectator->setSpectatorName($name);
        $spectator->setEmail($email);
        $spectator->setPassword(password_hash($password, PASSWORD_DEFAULT));

        try {
            $this->emi->persist($spectator);
            $this->emi->flush();
        }
        
        catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException) {
            $this->addFlash('error', 'Ezzel az e-maillel már létezik fiók!');
            return $this->redirectToRoute('event_page', ['m' => 'login', 'email' => $email]);
        }

        $sessionInterface->set('spectator_id', $spectator->getId());
        $this->addFlash('success', 'Sikeres regisztráció!');
        return $this->redirectToRoute('event_page');
    }

    #[Route('/login', name: 'event_login', methods:['POST'])]
    public function login(Request $request, SessionInterface $sessionInterface) {
        if (!$this->isCsrfTokenValid('login', (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Érvénytelen biztonsági token!');
            return $this->redirectToRoute('event_page', ['m' => 'login']);
        }

        $email = trim((string) $request->request->get('email'));
        $password = (string) $request->request->get('password');

        if (!preg_match('/^[\w\.-]+@[\w.-]+\.\w{2,6}$/', $email)) {
            $this->addFlash('error', 'Érvénytelen e-mail formátum!');
            return $this->redirectToRoute('event_page', ['m' => 'login']);
        }

        if ($email === '' || $password === '') {
            $this->addFlash('error', 'Kérlek az összes mezőt töltsd ki!');
            return $this->redirectToRoute('event_page', ['m' => 'login', 'email' => $email]);
        }

        $spectator = $this->spectatorRepository->findOneBy(['email' => $email]);
        if (!$spectator || !password_verify($password, (string) $spectator->getPassword())) {
            $this->addFlash('error', 'Hibás felhasználónév vagy jelszó!');
            return $this->redirectToRoute('event_page', ['m' => 'login', 'email' => $email]);
        }

        $sessionInterface->set('spectator_id', $spectator->getId());
        $this->addFlash('success', 'Sikeres bejelentkezés!');
        return $this->redirectToRoute('event_page');
    }

    #[Route('/logout', name: 'event_logout', methods: ['POST'])]
    public function logout(Request $request, SessionInterface $sessionInterface) : RedirectResponse {
        if (!$this->isCsrfTokenValid('logout', (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Érvénytelen biztonsági token!');
            $this->redirectToRoute('event_page');
        }

        $sessionInterface->remove('spectator_id');
        $this->addFlash('success', 'Ön kijelentkezett!');
        return $this->redirectToRoute('event_page');
    }

    #[Route('/registration/add', name: 'add_registration', methods:['POST'])]
    public function addRegistration(Request $request, SessionInterface $sessionInterface): RedirectResponse {
        if (!$this->isCsrfTokenValid('reg_add', (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Érvénytelen biztonsági token!');
            $this->redirectToRoute('event_page');
        }
        
        $spectatorId = $sessionInterface->get('spectator_id');
        if (!$spectatorId) {
            $this->addFlash('error', 'Előbb jelentkezzen be!');
            return $this->redirectToRoute('event_page', ['m' => 'login']);
        }

        $spectator = $this->spectatorRepository->find($spectatorId);
        $runEvent = $this->runEventRepository->find(1);

        if (!$spectator || !$runEvent) {
            $this->addFlash('error', 'Nem adta meg a specifikus eseményt vagy felhasználót!');
            return $this->redirectToRoute('event_page');
        }

        $registration = $this->registrationService->addOrGetExisting($spectator, $runEvent);

        $message = $registration->getStatus() === RegistrationStatus::STANDBY 
            ? 'Sikeres regisztráció! (kapacitáson belül)'
            : sprintf('Felkerültél a várólistára. Pozíciód: %d', $registration->getWaitlistPosition());
        
            $this->addFlash('success', $message);
            return $this->redirectToRoute('event_page');
    }

    #[Route('/registration/{id}/delete', name: 'delete_registration', methods:['POST'])]
    public function delete(int $id, Request $request, SessionInterface $sessionInterface): RedirectResponse {
        if (!$this->isCsrfTokenValid('reg_del_' . $id, (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Érvénytelen biztonsági token!');
            $this->redirectToRoute('event_page');
        }
        
        $spectatorId = $sessionInterface->get('spectator_id');
        if (!$spectatorId) {
            $this->addFlash('error', 'Előbb jelentkezzen be!');
            return $this->redirectToRoute('event_page', ['m' => 'login']);
        }

        $registration = $this->registrationRepository->find($id);
        if (!$registration) {
            $this->addFlash('error', 'Ezen az id-n nem történt regisztráció!');
            return $this->redirectToRoute('event_page');
        }

        if ($registration->getSpectator()->getId() !== $spectatorId) {
            $this->addFlash('error', 'Nincs jogosultságod ezt törölni!');
            return $this->redirectToRoute('event_page');
        }

        $this->registrationService->deleteAndPromoteOneMember($registration);
        $this->addFlash('success', 'Regisztréció törölve!');
        return $this->redirectToRoute('event_page');
    }
}
?>