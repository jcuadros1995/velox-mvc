<?php

class PublicPageController extends Controller
{
    private PublicPageRepository $pages;
    private EventRepository $events;
    private BranchRepository $branches;
    private NotificationService $notifications;
    private EmailService $email;

    public function __construct()
    {
        $this->pages = new PublicPageRepository();
        $this->events = new EventRepository();
        $this->branches = new BranchRepository();
        $this->notifications = new NotificationService();
        $this->email = new EmailService();
    }

    public function eventsAlias(): void
    {
        $this->eventPage();
    }

    public function show(string $slug): void
    {
        $slug=trim($slug,'/');
        if($slug==='contact'){ $this->contactPage(); return; }
        if($slug==='eventos-burger-town-bogota'){ $this->eventPage(); return; }
        $page=$this->pages->findBySlug($slug,true);
        if(!$page){http_response_code(404);echo 'Página no encontrada.';return;}
        $this->view('layouts/module_public_layout',[
            'contentView'=>__DIR__.'/../Views/pages/site-page.php',
            'publicPage'=>$page
        ]);
    }

    private function contactPage(): void
    {
        $this->view('layouts/module_public_layout',[
            'contentView'=>__DIR__.'/../Views/pages/contact.php',
            'script'=>'contact',
            'publicBranches'=>$this->branches->getActiveBranches()
        ]);
    }

    private function eventPage(): void
    {
        $this->view('layouts/module_public_layout',[
            'contentView'=>__DIR__.'/../Views/pages/events.php',
            'script'=>'events',
            'publicBranches'=>$this->branches->getActiveBranches()
        ]);
    }

    public function contactSubmit(): void
    {
        try {
            $data=$this->getJsonInput();
            $name=trim((string)($data['name']??''));
            $email=trim((string)($data['email']??''));
            $phone=trim((string)($data['phone']??''));
            $subject=trim((string)($data['subject']??'Consulta Burger Town'));
            $message=trim((string)($data['message']??''));
            if($name===''||mb_strlen($name)>120) throw new DomainException('Ingresa un nombre válido.',422);
            if(!filter_var($email,FILTER_VALIDATE_EMAIL)) throw new DomainException('Ingresa un correo válido.',422);
            if($message===''||mb_strlen($message)<10) throw new DomainException('Cuéntanos un poco más sobre tu solicitud.',422);
            $id=$this->pages->createContact(compact('name','email','phone','subject','message'));
            $recipient=SiteConfig::get('website','contact_email','contacto@burgertown.com.co');
            $this->email->sendContactMessage($recipient,compact('name','email','phone','subject','message','id'));
            $this->email->sendContactConfirmation($email,$name,compact('subject','message'));
            $this->json(['success'=>true,'message'=>'Tu mensaje fue enviado correctamente. Nuestro equipo te contactará pronto.']);
        } catch(DomainException $e){$this->json(['success'=>false,'message'=>$e->getMessage()],$e->getCode()?:422);}
          catch(Throwable $e){error_log('[CONTACT] '.$e->getMessage());$this->json(['success'=>false,'message'=>'No fue posible enviar tu mensaje. Intenta nuevamente.'],500);}
    }

    public function eventSubmit(): void
    {
        try {
            $data=$this->getJsonInput();
            $name=trim((string)($data['name']??''));
            $email=trim((string)($data['email']??''));
            $phone=trim((string)($data['phone']??''));
            $branchId=(int)($data['branch_id']??0);
            $eventType=trim((string)($data['event_type']??''));
            $eventDate=trim((string)($data['event_date']??''));
            $eventTime=trim((string)($data['event_time']??''));
            $guests=(int)($data['guests']??0);
            $message=trim((string)($data['message']??''));
            if($name===''||mb_strlen($name)>120) throw new DomainException('Ingresa un nombre válido.',422);
            if(!filter_var($email,FILTER_VALIDATE_EMAIL)) throw new DomainException('Ingresa un correo válido.',422);
            if($phone===''||mb_strlen($phone)>40) throw new DomainException('Ingresa un teléfono válido.',422);
            $branch=$this->branches->findById($branchId);
            if(!$branch||!(int)$branch['is_active']) throw new DomainException('Selecciona una sede válida.',422);
            if($eventType==='') throw new DomainException('Selecciona el tipo de evento.',422);
            $dt=DateTime::createFromFormat('Y-m-d',$eventDate);
            if(!$dt||$dt->format('Y-m-d')!==$eventDate||$eventDate<date('Y-m-d')) throw new DomainException('Selecciona una fecha válida.',422);
            if(!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/',$eventTime)) throw new DomainException('Selecciona una hora válida.',422);
            if($guests<2||$guests>500) throw new DomainException('La cantidad de invitados debe estar entre 2 y 500.',422);
            $id=$this->events->create(['branch_id'=>$branchId,'name'=>$name,'email'=>$email,'phone'=>$phone,'event_type'=>$eventType,'event_date'=>$eventDate,'event_time'=>$eventTime,'guests'=>$guests,'message'=>$message]);
            $event=$this->events->find($id);

            $branchEmail = trim(
                (string) ($branch['email'] ?? '')
            );

            // Notificación interna al administrador de la sede.
            // Se conserva el comportamiento de las notificaciones internas.
            $admins = $this->events->branchAdmins($branchId);

            foreach ($admins as $admin) {
                $this->notifications->create(
                    (int) $admin['id'],
                    $branchId,
                    'event_request',
                    'Nuevo evento solicitado',
                    'Solicitud de evento de ' . $name . ' para ' . $eventDate . '.',
                    'event_request',
                    $id
                );
            }

            // El correo administrativo se envía exclusivamente
            // al correo configurado en la sede del evento.
            if (
                $branchEmail !== '' &&
                filter_var($branchEmail, FILTER_VALIDATE_EMAIL)
            ) {
                $this->email->sendEventRequestAdmin(
                    $event,
                    [
                        'email' => $branchEmail,
                        'first_name' => 'Equipo',
                        'last_name' => $branch['name'] ?? ''
                    ]
                );
            }

            // La confirmación al cliente permanece sin cambios.
            $this->email->sendEventConfirmation($event);

            $this->json(['success'=>true,'message'=>'Recibimos tu solicitud de evento. La sede se pondrá en contacto contigo para confirmar disponibilidad.','id'=>$id]);
        } catch(DomainException $e){$this->json(['success'=>false,'message'=>$e->getMessage()],$e->getCode()?:422);}
          catch(Throwable $e){error_log('[EVENT REQUEST] '.$e->getMessage());$this->json(['success'=>false,'message'=>'No fue posible registrar el evento. Intenta nuevamente.'],500);}
    }
    private function getJsonInput(): array
    {
        $data = json_decode(file_get_contents('php://input'), true);
        return is_array($data) ? $data : [];
    }
}
