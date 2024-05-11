<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use App\Models\Guru;
use Spatie\DiscordAlerts\Facades\DiscordAlert;

class GuruController extends Controller
{
    public function getAccessToken()
    {
        $url = sprintf('%s/oauth/token/access', env('GURU_BASE_URL'));

        $request_body = [
            'grant_type' => 'client_credentials',
            'client_id' => env('GURU_CLIENT_ID'),
            'client_secret' => env('GURU_CLIENT_SECRET')
        ];


        $response = Http::withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->asForm()->post($url, $request_body);
        $response = json_decode($response);

        dump($response);
        $this->changeEnvironmentVariable('GURU_ACCESS_TOKEN', $response->access_token);
        $this->changeEnvironmentVariable('GURU_REFRESH_TOKEN', $response->refresh_token);

        DiscordAlert::message("New Token: " . $response->access_token);
        return $response;

    }

    public function getNewAccessTokenFromRefreshToken()
    {
        $url = sprintf('%s/oauth/token/access', env('GURU_BASE_URL'));

        $request_body = [
            'grant_type' => 'refresh_token',
            'refresh_token' => env('GURU_REFRESH_TOKEN'),
            'client_id' => env('GURU_CLIENT_ID'),
        ];
        $response = Http::withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->asForm()->post($url, $request_body);
        $response = json_decode($response);

        dump($response);
        $this->changeEnvironmentVariable('GURU_ACCESS_TOKEN', $response->access_token);
        $this->changeEnvironmentVariable('GURU_REFRESH_TOKEN', $response->refresh_token);

        DiscordAlert::message("Token Refreshed");
        return $response;

    }

    public static function changeEnvironmentVariable($key, $value)
    {
        $path = base_path('.env');

        if (file_exists($path)) {

            file_put_contents($path, str_replace(
                $key.'='.env($key), $key.'='.$value, file_get_contents($path)
            ));
        }
    }

    public function store_jobs()
    {
        $url = sprintf('%s/search/job?category=programming-development&pagesize=300', env('GURU_BASE_URL'));

         $response = Http::withToken(env('GURU_ACCESS_TOKEN'))->get($url);

        //$response = file_get_contents(public_path('jobs.json'));

        $response = json_decode($response);
        dump($response);
        $response = $this->filterNonRelevantJobs($response);

    }

    public function filterNonRelevantJobs($response){

        $excluded_categories = [
            'Apps & Mobile',
            'Industry Specific Expertise',
            'Math / Algorithms / Analytics',
            'Technical Support / Help Desk (Hardware / Software) (11)',
            'QA & Testing',
            'Web / Digital Marketing',
            'Concepts / Ideas / Documentation',
            'Games (2D / 3D / Mobile)',
            'Telephony / Telecommunications',
            'Blockchain, NFT, Cryptocurrency, Tokens'
        ];

        $limit = 1;

        # Filtering out non-relevant jobs from the json response
        $filtered_jobs = [];
        foreach ($response->Results as $job) {
            if (!in_array($job->JobSubCategory, $excluded_categories)) {
                $filtered_jobs[] = $job;

                $guru = Guru::where('job_id', $job->JobId)->first();

                if($guru) continue;

                #Create or update the job in the database
                $data = [
                    'job_id' => $job->JobId,
                    'title' => $job->JobTitle,
                    'description' => $job->JobDescription,
                    'subcategory' => $job->JobSubCategory,
                    'country' => $job->EmployerCountryName,

                ];

                Guru::create(
                    $data
                );

                dump(sprintf('Job ID: %s', $job->JobId));
                $this->sendDiscordMessage($data);

                $limit++;
                // if($limit == 4) break;
  }
        }

        return $filtered_jobs;
    }

    public function delete_old_jobs(){
        $deleted_jobs = \App\Models\Guru::where('created_at', '<', now()->subDays(30))->delete();
        $msg = "Deleted $deleted_jobs old jobs";
        DiscordAlert::message($msg);
    }

    function sendDiscordMessage($job){
        $markdown = '';
        $markdown = '# ' . $job['title'] . "  \n";
        $markdown .= '- ' . $job['subcategory']. "  \n";
        $markdown .= '- ' . $job['country'] . "  \n";
        $markdown .= '- ' . $job['description'] . "  \n";
        $markdown .= '- https://www.guru.com/work/detail/' . $job['job_id'] . "  \n";


        DiscordAlert::message($markdown);
    }
}
