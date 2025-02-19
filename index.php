<!DOCTYPE html>
<html>
  <head>
    <title>Google Calendar API Quickstart</title>
    <meta charset="utf-8" />
    <style>
        .container {
            display: flex;
            gap: 20px;
        }

        .column {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .item {
            padding: 10px;
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
    </style>
  </head>
  <body>
  <script  type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/2.2.3/jquery.min.js"></script>
    <p>Google Calendar API Quickstart</p>

    <!--Add buttons to initiate auth sequence and sign out-->
    <button id="authorize_button" onclick="handleAuthClick()">Authorize</button>
    <button id="signout_button" onclick="handleSignoutClick()">Sign Out</button>
    <button onclick="exec()">Adicionar evento</button>

    <pre id="content" style="white-space: pre-wrap;"></pre>
    <div class="container">
        <div class="column" id="column1">
            <!-- Adicione as divs aqui -->
            <?php 
            include_once('config.php');
              $queryReuest = mysqli_query($mysqli, "SELECT * FROM requests");
              if(mysqli_num_rows( $queryReuest) > 0){
                  
                  while($row = mysqli_fetch_assoc( $queryReuest)){
                      $IDrequest = $row['id_request'];
                      $contentrequest = $row['message'];
                      echo '<div class="item">'.$IDrequest.' - '.$contentrequest.'</div>'; 
                  }
              }else{
                echo '<div class="item">sem requisições</div>'; 
              }
            ?>
        </div>
      </div>

      
    <script type="text/javascript">

let API_KEY, CLIENT_ID;
      // TODO(developer): Set to client ID and API key from the Developer Console
  async function loadGoogleConfig() {
    try {
    
    const response = await fetch('get-google-config.php');

    if (!response.ok) {
      throw new Error("Erro ao carregar a configuração: " + response.status);
    }

    const config = await response.json();
    

    API_KEY = config.apiKey;
    CLIENT_ID = config.clientId;

    console.log("API_KEY:", API_KEY, "CLIENT_ID:", CLIENT_ID); // Debug para verificar carregamento

  } catch (error) {
    console.error("Erro ao buscar configuração:", error);
  }
}
async function start() {
  await loadGoogleConfig(); 
  gapiLoaded(); 
}

start();

gapiLoaded = loadGoogleConfig;
      const DISCOVERY_DOC = 'https://www.googleapis.com/discovery/v1/apis/calendar/v3/rest';

      // Authorization scopes required by the API; multiple scopes can be
      // included, separated by spaces.
      const SCOPES = 'https://www.googleapis.com/auth/calendar';

      let tokenClient;
      let gapiInited = false;
      let gisInited = false;

      document.getElementById('authorize_button').style.visibility = 'hidden';
      document.getElementById('signout_button').style.visibility = 'hidden';

      /**
       * Callback after api.js is loaded.
       */
       function gapiLoaded() {

        gapi.load('client', async () => {
    try {
      console.log("✅ GAPI Client carregado.");
      
      // Aguardar a inicialização do gapi.client com as configurações
      await gapi.client.init({
        apiKey: API_KEY,
        clientId: CLIENT_ID,
        discoveryDocs: [DISCOVERY_DOC],
        scope: SCOPES
      });

      console.log("✅ GAPI Client inicializado.");

      // Agora que a API foi inicializada, você pode chamar outras funções
      gapiInited = true;
      maybeEnableButtons(); // Habilite os botões ou o que for necessário.
    } catch (error) {
      console.error("❌ Erro ao carregar o GAPI Client:", error);
    }
  });
      }

      /**
       * Callback after the API client is loaded. Loads the
       * discovery doc to initialize the API.
       */
      async function initializeGapiClient() {
        await loadGoogleConfig();
        console.log(API_KEY, CLIENT_ID)
        await gapi.client.init({
          apiKey: API_KEY,
          discoveryDocs: [DISCOVERY_DOC],
        });
        gapiInited = true;
        maybeEnableButtons();
      }

      /**
       * Callback after Google Identity Services are loaded.
       */
      async function gisLoaded() {
        
        await loadGoogleConfig(); 
        console.log(CLIENT_ID)
        tokenClient = google.accounts.oauth2.initTokenClient({
          client_id: CLIENT_ID,
          scope: SCOPES,
          callback: '', // defined later
        });
        gisInited = true;
        maybeEnableButtons();
      }

      /**
       * Enables user interaction after all libraries are loaded.
       */
      function maybeEnableButtons() {
        console.log("gapiInited:", gapiInited, "gisInited:", gisInited);

        if (gapiInited && gisInited) {
          document.getElementById('authorize_button').style.visibility = 'visible';
        }
      }

      /**
       *  Sign in the user upon button click.
       */
      function handleAuthClick() {
        tokenClient.callback = async (resp) => {
          if (resp.error !== undefined) {
            throw (resp);
          }
          document.getElementById('signout_button').style.visibility = 'visible';
          document.getElementById('authorize_button').innerText = 'Refresh';
          await listUpcomingEvents();
        };

        if (gapi.client.getToken() === null) {
          // Prompt the user to select a Google Account and ask for consent to share their data
          // when establishing a new session.
          tokenClient.requestAccessToken({prompt: 'consent'});
        } else {
          // Skip display of account chooser and consent dialog for an existing session.
          tokenClient.requestAccessToken({prompt: ''});
        }
      }

      /**
       *  Sign out the user upon button click.
       */
      function handleSignoutClick() {
        const token = gapi.client.getToken();
        if (token !== null) {
          google.accounts.oauth2.revoke(token.access_token);
          gapi.client.setToken('');
          document.getElementById('content').innerText = '';
          document.getElementById('authorize_button').innerText = 'Authorize';
          document.getElementById('signout_button').style.visibility = 'hidden';
        }
      }

      /**
       * Print the summary and start datetime/date of the next ten events in
       * the authorized user's calendar. If no events are found an
       * appropriate message is printed.
       */
      async function listUpcomingEvents() {
        let response;
        try {
          const request = {
            'calendarId': 'primary',
            'timeMin': (new Date()).toISOString(),
            'showDeleted': false,
            'singleEvents': true,
            'maxResults': 10,
            'orderBy': 'startTime',
          };
          response = await gapi.client.calendar.events.list(request);
        } catch (err) {
          document.getElementById('content').innerText = err.message;
          return;
        }

        const events = response.result.items;
        console.log(response)
        if (!events || events.length == 0) {
          document.getElementById('content').innerText = 'No events found.';
          return;
        }
        // Flatten to string to display
        const output = events.reduce(
            (str, event) => `${str}${event.summary} (${event.start.dateTime || event.start.date})\n`,
            'Events:\n');
        document.getElementById('content').innerText = output;
      }
function exec(){
      const event = {
  'summary': 'Google I/O 2015',
  'location': '800 Howard St., San Francisco, CA 94103',
  'description': 'A chance to hear more about Google\'s developer products.',
  'start': {
    'dateTime': '2025-01-14T09:00:00-07:00',
    'timeZone': 'America/Los_Angeles'
  },
  'end': {
    'dateTime': '2025-01-15T17:00:00-07:00',
    'timeZone': 'America/Los_Angeles'
  },
};

const request = gapi.client.calendar.events.insert({
  'calendarId': 'primary',
  'resource': event
});

  request.execute(function(event) {
    document.getElementById('content').innerText = 'Event created: ' + event.htmlLink;
});
}
    </script>
    <script async defer src="https://apis.google.com/js/api.js"  onload="gapiLoaded()"></script>
    <script async defer src="https://accounts.google.com/gsi/client" onload="gisLoaded()"></script>
    <script>
  window.onload = function () {
    console.log("🔄 Chamando gapiLoaded manualmente...");
    gapiLoaded();
  };
</script>
  </body>
</html>