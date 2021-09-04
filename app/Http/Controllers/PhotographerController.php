<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\User;
use App\UserAppointment;
use App\UserFavorite;
use App\Photographer;
use App\PhotographerPhotos;
use App\PhotographerServices;
use App\PhotographerTestimonial;
use App\PhotographerAvailability;


class PhotographerController extends Controller
{
    private $loggedUser;

    public function middleware($middleware, array $options = []){}

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->loggedUser = auth()->user();
    }
    /*
    public function createRandom()
    {
        $array = ['error' => ''];
        for ($q = 0; $q < 15; $q++) {
            $names = ['Nathan', 'Paulo', 'Pedro', 'Amanda', 'Leticia', 'Gabriel', 'Gabriela', 'Thais', 'Luiz', 'Rhaôny', 'José', 'Jeremias', 'Francisco', 'Dirce', 'Marcelo'];
            $lastnames = ['Santos', 'Silva', 'Santos', 'Silva', 'Alvaro', 'Sousa', 'Diniz', 'Josefa', 'Luiz', 'Diogo', 'Limoeiro', 'Santos', 'Limiro', 'Nazare', 'Mimoza'];
            $servicos = ['Ensaios', 'Editoriais'];
            $servicos2 = ['15 anos', 'moda', 'empresas', 'mulheres', 'homens', 'crianças', 'gestantes'];
            $depos = [
                'Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptate consequatur tenetur facere voluptatibus iusto accusantium vero sunt, itaque nisi esse ad temporibus a rerum aperiam cum quaerat quae quasi unde.',
                'Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptate consequatur tenetur facere voluptatibus iusto accusantium vero sunt, itaque nisi esse ad temporibus a rerum aperiam cum quaerat quae quasi unde.',
                'Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptate consequatur tenetur facere voluptatibus iusto accusantium vero sunt, itaque nisi esse ad temporibus a rerum aperiam cum quaerat quae quasi unde.',
                'Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptate consequatur tenetur facere voluptatibus iusto accusantium vero sunt, itaque nisi esse ad temporibus a rerum aperiam cum quaerat quae quasi unde.',
                'Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptate consequatur tenetur facere voluptatibus iusto accusantium vero sunt, itaque nisi esse ad temporibus a rerum aperiam cum quaerat quae quasi unde.'
            ];
            $newPhotographer = new Photographer();
            $newPhotographer->name = $names[rand(0, count($names) - 1)] . ' ' . $lastnames[rand(0, count($lastnames) - 1)];
            $newPhotographer->avatar = rand(1, 4) . '.png';
            $newPhotographer->stars = rand(2, 4) . '.' . rand(0, 9);
            $newPhotographer->latitude = '-27.5' . rand(0, 9) . '30907';
            $newPhotographer->longitude = '-48.6' . rand(0, 9) . '82759';
            $newPhotographer->save();
            $ns = rand(3, 6);
            for ($w = 0; $w < 4; $w++) {
                $newPhotographerPhoto = new PhotographerPhotos();
                $newPhotographerPhoto->id_photographer = $newPhotographer->id;
                $newPhotographerPhoto->url = rand(1, 5) . '.png';
                $newPhotographerPhoto->save();
            }
            for ($w = 0; $w < $ns; $w++) {
                $newPhotographerService = new PhotographerServices();
                $newPhotographerService->id_photographer = $newPhotographer->id;
                $newPhotographerService->name = $servicos[rand(0, count($servicos) - 1)] . ' de ' . $servicos2[rand(0, count($servicos2) - 1)];
                $newPhotographerService->price = rand(1, 99) . '.' . rand(0, 100);
                $newPhotographerService->save();
            }
            for ($w = 0; $w < 3; $w++) {
                $newPhotographerTestimonial = new PhotographerTestimonial();
                $newPhotographerTestimonial->id_photographer = $newPhotographer->id;
                $newPhotographerTestimonial->name = $names[rand(0, count($names) - 1)];
                $newPhotographerTestimonial->rate = rand(2, 4) . '.' . rand(0, 9);
                $newPhotographerTestimonial->body = $depos[rand(0, count($depos) - 1)];
                $newPhotographerTestimonial->save();
            }
            for ($e = 0; $e < 4; $e++) {
                $rAdd = rand(7, 10);
                $hours = [];
                for ($r = 0; $r < 8; $r++) {
                    $time = $r + $rAdd;
                    if ($time < 10) {
                        $time = '0' . $time;
                    }
                    $hours[] = $time . ':00';
                }
                $newPhotographerAvail = new PhotographerAvailability();
                $newPhotographerAvail->id_photographer = $newPhotographer->id;
                $newPhotographerAvail->weekday = $e;
                $newPhotographerAvail->hours = implode(',', $hours);
                $newPhotographerAvail->save();
            }
        }
        return $array;
    }
    */

    private function searchGeo($address) {
        $key = env('MAPS_KEY', null);

        $address = urlencode($address);

        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address='.$address.'&key='.$key;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($ch);
        curl_close($ch);

        return json_decode($res, true);
    }

    public function list(Request $request) {
        $array = ['error' => ''];

        $lat = $request->input('lat');
        $lng = $request->input('lng');
        $city = $request->input('city');
        $offset = $request->input('offset');

        if(!$offset) {
            $offset = 0;
        }

        if(!empty($city)){
            $res = $this->searchGeo($city);

            if(count($res['results']) > 0) {
                $lat = $res['results'][0]['geometry']['location']['lat'];
                $lng = $res['results'][0]['geometry']['location']['lng'];
            }
        } elseif(!empty($lat) && !empty($lng)) {
            $res = $this->searchGeo($lat.','.$lng);

            if(count($res['results']) > 0) {
                $city = $res['results'][0]['formatted_adderss'];
            }
        } else {
            $lat = '-27.6022759';
            $lng = '-48.5417298';
            $city = 'Florianópolis';
        }

        $photographers = Photographer::select(Photographer::raw(
            '*, SQRT(
            POW(69.1 * (latitude - '.$lat.'), 2) + 
            POW(69.1 * ('.$lng.' - longitude) * COS(latitude / 57.3), 2)) AS distance'))
            ->havingRaw('distance < ?', [15])
            ->orderBy('distance', 'ASC')
            ->offset($offset)
            ->limit(5)
            ->get();

        foreach ($photographers as $pkey => $pvalue) {
            $photographers[$pkey]['avatar'] = url('media/avatars/'.$photographers[$pkey]['avatar']);
        }

        $array['data'] = $photographers;
        $array['loc'] = 'Florianópolis';

        return $array;
    }

    public function one($id) {
        $array = ['error' => ''];

        $photographer = Photographer::find($id);

        if($photographer) {
            $photographer['avatar'] = url('media/avatars/'.$photographer['avatar']);
            $photographer['favorited'] = false;
            $photographer['photos'] = [];
            $photographer['services'] = [];
            $photographer['testimonials'] = [];
            $photographer['available'] = [];

            //UserFavorite Verificando favorito
            $cFavorite = UserFavorite::where('id_user', $this->loggedUser->id)
                ->where('id_photographer', $photographer->id)
                ->count();
            if($cFavorite > 0) {
                $photographer['favorited'] = true;
            } 

            // Fotos
            $photographer['photos'] = PhotographerPhotos::select('id', 'url')->where('id_photographer', $photographer->id)->get();
            foreach ($photographer['photos'] as $ppkey => $ppvalue) {
                $photographer['photos'][$ppkey]['url'] = url('media/uploads/'.$photographer['photos'][$ppkey]['url']);
            }

            // Serviços
            $photographer['services'] = PhotographerServices::select('id', 'name', 'price')->where('id_photographer', $photographer->id)->get();

            // Depoimentos
            $photographer['testimonials'] = PhotographerTestimonial::select('id', 'name', 'rate', 'body')->where('id_photographer', $photographer->id)->get();

            // Disponibilidade do photographer
            $availability = [];

            // Disponibilidade no BD
            $avails= PhotographerAvailability::where('id_photographer', $photographer->id)->get();
            $availWeekdays = [];
            foreach($avails as $item) {
                $availWeekdays[$item['weekday']] = explode(',', $item['hours']);
            }

            // - Ver os agendamentos dos proximos 60 dias
            $appointments = [];
            $appQuery = UserAppointment::where('id_photographer', $photographer->id)->whereBetween('ap_datetime', [
                date('Y-m-d').' 00:00:00',
                date('Y-m-d', strtotime('+60 days')).' 23:59:59'
            ])->get();

            foreach($appQuery as $appItem) {
                $appointments[] = $appItem['ap_datetime'];
            }

            // Lista de disponibilidades
            for($q=0;$q<60;$q++) {
                $timeItem = strtotime('+'.$q.' days');
                $weekday = date('w', $timeItem);

                if(in_array($weekday, array_keys($availWeekdays))) {
                    $hours = [];

                    $dayItem = date('Y-m-d', $timeItem);

                    foreach($availWeekdays[$weekday] as $hourItem) {
                        $dayFormated = $dayItem. ' '.$hourItem. ':00';
                        if(!in_array($dayFormated, $appointments)) {
                            $hours[] = $hourItem;
                        }
                    }

                    if(count($hours) > 0) {
                        $availability[] = [
                            'date' => $dayItem,
                            'hours' => $hours
                        ];
                    }
                }
            }

            $photographer['available'] = $availability;

            $array['data'] = $photographer;
        } else {
            $array['error'] = 'Fotógrafo não existe';
            return $array;
        }

        return $array;
    }

    public function setAppointment($id, Request $request) {
        $array = ['error' => ''];

        $service = $request->input('service');
        $year = intval($request->input('year'));
        $month = intval($request->input('month'));
        $day = intval($request->input('day'));
        $hour = intval($request->input('hour'));

        $month = ($month < 10) ? '0'.$month : $month;
        $day = ($day < 10) ? '0'.$day : $day;
        $hour = ($hour < 10) ? '0'.$hour : $hour;

         // 1. verificar se o serviço do fotografo existe
         $photographerservice = PhotographerServices::select()
         ->where('id', $service)
         ->where('id_photographer', $id)
     ->first();

     if($photographerservice) {
         // 2. verificar se a data é real
         $apDate = $year.'-'.$month.'-'.$day.' '.$hour.':00:00';
         if(strtotime($apDate) > 0) {
             // 3. verificar se o fotografo já possui agendamento neste dia/hora
             $apps = UserAppointment::select()
                 ->where('id_photographer', $id)
                 ->where('ap_datetime', $apDate)
             ->count();
             if($apps === 0) {
                 // 4.1 verificar se o fotografo atende nesta data
                 $weekday = date('w', strtotime($apDate));
                 $avail = PhotographerAvailability::select()
                     ->where('id_photographer', $id)
                     ->where('weekday', $weekday)
                 ->first();
                 if($avail) {
                     // 4.2 verificar se o fotografo atende nesta hora
                     $hours = explode(',', $avail['hours']);
                     if(in_array($hour.':00', $hours)) {
                         // 5. fazer o agendamento
                         $newApp = new UserAppointment();
                         $newApp->id_user = $this->loggedUser->id;
                         $newApp->id_photographer = $id;
                         $newApp->id_service = $service;
                         $newApp->ap_datetime = $apDate;
                         $newApp->save();
                     } else {
                         $array['error'] = 'Fotógrafo não atende nesta hora';
                     }
                 } else {
                     $array['error'] = 'Fotógrafo não atende neste dia';
                 }                    
             } else {
                 $array['error'] = 'Fotógrafo já possui agendamento neste dia/hora';
             }
         } else {
             $array['error'] = 'Data inválida';
         }
     } else {
         $array['error'] = 'Serviço inexistente!';
     }
     return $array;
    }

     public function search(Request $request) {
        $array = ['error'=>'', 'list'=>[]];

        $q = $request->input('q');

        if($q) {

            $photographers = Photographer::select()
                ->where('name', 'LIKE', '%'.$q.'%')
            ->get();

            foreach($photographers as $pkey => $photographer) {
                $photographers[$pkey]['avatar'] = url('media/avatars/'.$photographers[$pkey]['avatar']);
            }

            $array['list'] = $photographers;
        } else {
            $array['error'] = 'Digite algo para buscar';
        }

        return $array;
    }
}
