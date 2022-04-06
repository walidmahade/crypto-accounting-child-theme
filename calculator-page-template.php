<?php
/*
 * Template Name: Calculator Page
 */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Crypto Accounting - Priskalkulator</title>
    <meta name="description" content="Finn din pris på utfylling av RF 1159 skjema på skattemeldingen" />
    <link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri() ?>/style/jquery-ui.min.css" />
    <link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri() ?>/style/calc.css">
    <!-- fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
</head>

<body>
<section class="calculator" id="app">
    <a class="crypto-logo" href="/" style="display: block;">
        <img src="<?php echo get_stylesheet_directory_uri() ?>/image/logo.svg" alt="crypto-logo" />
    </a>

    <div class="container visible" id="calc-1">
        <div class="text">
            <h1 class="heading heading-top">Hvilke tjenester har du benyttet deg av i 2021?</h1>
        </div>

        <div class="checkbox-items">
            <template v-for="option in options">
                <div class="group" >
                    <input type="checkbox" :id="option.id" :value="option.id" v-model="choices"/>
                    <label :for="option.id" v-text="option.title"></label>
                </div>
            </template>
        </div>
        <!-- checkbox-items -->

        <div class="text">
            <h1 class="heading heading-bottom">Hvor mange transaksjoner finner vi i lommebøkene og på børsene du handler?</h1>
        </div>

        <div id="mw-range-slider">
            <div class="current-value">500</div>

            <div id="custom-handle" class="ui-slider-handle"></div>

            <div class="fake-bar"></div>

            <div class="custom-range">
                <div class="start">500</div>
                <div class="end">4000+</div>
            </div>
        </div>

        <div class="button-wrapper">
            <button class="deliver-btn" :class="[{ 'deliver-btn-disabled': choices.length < 1 || choices.indexOf('1') === -1 }]"
                id="go-to-next-step" @click="goTextStep()">
              Send meg et tilbud</button>
        </div>
    </div>

    <div class="container" id="calc-2">
        <div class="crypto-card">
            <h1 class="prices-heading">{{form.transactions !== '4000+' ? 'Dine kryptopriser' : 'Kontakt oss for pris' }}</h1>
            <h1 class="prices" v-show="form.transactions !== '4000+'">{{ form.sum }} kr</h1>
            <h4 v-show="form.transactions !== '4000+'">
              {{ NOKtoBTC }} BTC
            </h4>
            <h4 style="margin-bottom: 0;" v-show="form.transactions !== '4000+'">
              {{ NOKtoETH }} ETH
            </h4>
        </div>
        <div class="text">
            <h1 class="heading heading-top">Pris for</h1>
        </div>

        <div class="checkbox-items">
            <div v-for="label in chosenOptions" v-text="label.title">( Buy and sell )kjøp og salg</div>
            <div>Transaksjoner: {{form.transactions}}</div>
        </div>
        <!-- checkbox-items -->

        <div class=" form-group">
            <form>
                <label for="username">Navn</label>
                <input type="text" placeholder="Din navn" id="username" name="username" v-model="form.username">
                <br>
                <br>
                <label for="email">Epost</label>
                <input type="email" placeholder="Skriv din epost" id="email" name="email" v-model="form.email">
                <br>
                <br>
                <label for="phone">Telefon</label>
                <input type="number" placeholder="Din telefon" id="phone" name="phone" v-model="form.phone">
            </form>
        </div>

        <div class="button-wrapper">
            <button :class="[{ 'deliver-btn-disabled': !form.email || !form.username }, 'deliver-btn']"
                    @click.prevent="submitData()">
              {{ form.isLoading ? ' ' : 'Send meg tilbud' }}
              <div v-show="form.isLoading" class="loader">Loading...</div>
            </button>
        </div>

        <div class="messages"  v-show="!!form.message.error">
          <div class="error">{{form.message.error}}</div>
        </div>

        <div class="messages messages-final" v-show="!!form.message.success">
          <div class="image"><img src="<?php echo get_stylesheet_directory_uri() ?>/image/Check-mark.svg" alt="green check"></div>
          <div class="success">{{form.message.success}}</div>
        </div>
    </div>
</section>


<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://unpkg.com/vue@3"></script>
<script src="https://code.jquery.com/jquery-3.6.0.js"></script>
<script src="https://code.jquery.com/ui/1.13.1/jquery-ui.js"></script>

<?php
$transaction_cost = get_field('transaction_cost', 'option');
$user_options = get_field('user_options', 'option');
?>

<script>
  const transactionCost = {
    <?php
    foreach ($transaction_cost as $row) {
      echo '"'.$row['count'] . '": ' . $row['cost'].', ';
    }
    ?>
  }

  Vue.createApp({
    data() {
      return {
        contactFormId: 1509,
        baseUrl: 'https://cryptoaccounting.no',
        options:  <?php echo json_encode($user_options); ?>,
        choices: ['1'],
        chosenOptions: [],
        // this rates are against BTC
        cryptoExchangeRates: {
          'BTC': 1,
          'ETH': 1,
          'NOK': 1,
          'error': ''
        },
        form: {
          username: '',
          email: '',
          phone: '',
          sum: 7000,
          transactions: 0,
          isLoading: false,
          message: {
            error: '',
            success: ''
          }
        }
      }
    },
    methods: {
      goTextStep() {
        //console.log(transactionCost)
        this.form.transactions = $(".current-value").text();
        this.form.sum += Number(transactionCost[this.form.transactions]);
      },
      getCryptoExchangeRates() {
        axios.get(`https://api.coingecko.com/api/v3/exchange_rates`)
        .then((response) => {
          // console.log(response);
          this.cryptoExchangeRates['ETH'] = Number(response.data.rates['eth'].value);
          this.cryptoExchangeRates['NOK'] = Number(response.data.rates['nok'].value);
        })
        .catch((error) => {
          // console.log(error);
          this.cryptoExchangeRates['error'] = "Kunne ikke hente valutakurser.";
        });
      },
      submitData() {
        this.form.isLoading = true;

        let formData = new FormData();
        formData.append('username', this.form.username);
        formData.append('email', this.form.email);
        formData.append('phone', this.form.phone);
        formData.append('transactions', this.form.transactions);

        this.chosenOptions.forEach((item) => {
          formData.append('choices[]', `${item.title} : ${item.value}`);
        });

        formData.append('sum', this.form.sum);

        // send request to contact form 7
        axios.post(`${this.baseUrl}/wp-json/contact-form-7/v1/contact-forms/${this.contactFormId}/feedback`, formData)
        .then((response) => {
          //console.log(response);
          if (response.data.status === 'validation_failed') {
            this.form.isLoading = false;
            this.form.message.error = 'Ett eller flere felt har feil';
          } else {
            this.form.isLoading = false;
            this.form.message.success = 'Din forespørsel er sendt. Vi vil komme tilbake til deg så snart som mulig.';
          }
        })
        .catch(function (error) {
          //console.log(error);
          this.form.isLoading = false;
        });
      }
    },
    computed: {
      NOKtoBTC() {
        return (Number(this.form.sum) / Number(this.cryptoExchangeRates['NOK'])).toFixed(4)
      },
      NOKtoETH() {
        return (
            (Number(this.form.sum) * Number(this.cryptoExchangeRates['ETH'])) / Number(this.cryptoExchangeRates['NOK'])
        ).toFixed(4)
      },
    },
    watch: {
      choices(newChoice, old) {
        this.chosenOptions = [];
        this.form.sum = 0;
        for (let i = 0; i < this.choices.length; i++) {
          for(let j = 0; j < this.options.length; j++) {
            if (this.choices[i] === this.options[j].id) {
              this.chosenOptions.push(this.options[j]);
              this.form.sum += Number(this.options[j].value);
              break;
            }
          }
        }
      }
    },
    mounted() {
      this.chosenOptions.push(this.options[0]);
      this.getCryptoExchangeRates();
    }
  }).mount('#app')
</script>

<script>
  $(function () {
    /**
     * Slider code
     * @type {*|jQuery|HTMLElement}
     */
    let fakeBar = $(".fake-bar");
    let currentVal = $(".current-value");

    $("#mw-range-slider").slider({
      value: 500,
      step: 500,
      min: 500,
      max: 4500,
      create: function () {
        currentVal.text($(this).slider("value"));
        currentVal.css("left", 0 + "%");
        fakeBar.css("width", 0 + "%");
      },
      slide: function (event, ui) {
        let incrementer = (ui.value / 500) - 1
        if (ui.value > 4000) {
          $('#go-to-next-step').text('Kontakt oss for pris');
          currentVal.text('4000+');
        } else {
          $('#go-to-next-step').text('Send meg et tilbud');
          currentVal.text((ui.value));
        }
        if (incrementer < 7) {
          currentVal.css("left", (incrementer * 12.5) + "%");
        } else {
          if ($(window).width() < 768) {
            currentVal.css("left", "85%");
          } else {
            currentVal.css("left", "100%");
          }
        }
        fakeBar.css("width", (incrementer * 12.5) + "%");
      },
    });

    /**
     * calculator steps handle
     */
    const triggerNextStep = $("#go-to-next-step")
    triggerNextStep.click(function (e) {
      e.preventDefault();
      $("#calc-2").addClass("visible");
      $("#calc-1").removeClass("visible");
    })
  });
</script>
</body>

</html>
