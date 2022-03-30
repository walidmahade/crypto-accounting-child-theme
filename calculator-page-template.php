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
    <title>Crypto Accounting - Calculator</title>
    <link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri() ?>/style/jquery-ui.min.css" />
    <link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri() ?>/style/calc.css">
    <!-- fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
</head>

<body>
<section class="calculator" id="app">
    <div class="crypto-logo">
        <img src="<?php echo get_stylesheet_directory_uri() ?>/image/logo.svg" alt="crypto-logo" />
    </div>

    <div class="container visible" id="calc-1">
        <div class="text">
            <h1 class="heading heading-top">Send meg et tilbud</h1>
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
            <h1 class="heading heading-bottom">Hvor mange transaksjoner</h1>
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
            <button class="deliver-btn" :class="[{ 'deliver-btn-disabled': choices.length < 1 || choices.indexOf(1) === -1 }]"
                id="go-to-next-step" @click="goTextStep()">
              Send meg et tilbud</button>
        </div>
    </div>

    <div class="container" id="calc-2">
        <div class="crypto-card">
            <h1 class="prices-heading">Dine kryptopriser</h1>
            <h1 class="prices">{{ form.sum }} kr</h1>
        </div>
        <div class="text">
            <h1 class="heading heading-top">Se ditt resultat</h1>
        </div>

        <div class="checkbox-items">
            <div v-for="label in chosenOptions" v-text="label.title">( Buy and sell )kjøp og salg</div>
        </div>
        <!-- checkbox-items -->

        <div class=" form-group">
            <form>
                <label for="username">Nvan</label>
                <input type="text" placeholder="Skriv din epost" id="username" name="username" v-model="form.username">
                <br>
                <br>
                <label for="email">Epost</label>
                <input type="email" placeholder="Skriv din epost" id="email" name="email" v-model="form.email">
            </form>
        </div>

        <div class="button-wrapper">
            <button :class="[{ 'deliver-btn-disabled': !form.email || !form.username }, 'deliver-btn']"
                    @click.prevent="submitData()">
              {{ form.isLoading ? ' ' : 'Se ditt resultat' }}
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

<script>
  Vue.createApp({
    data() {
      return {
        contactFormId: 1509,
        baseUrl: 'https://crypto-acc.getonnet.dev',
        options: [
          { id: 1, title: 'kjøp og salg', value: 7000},
          { id: 2, title: 'Futures/leverage', value: 4000},
          { id: 3, title: 'Farming/Yield Farming/Liquidity Mining', value: 6000},
          { id: 4, title: 'Kjøp av noder eller lignende', value: 8000},
          { id: 5, title: 'NFT`er', value: 5000},
          { id: 6, title: 'Fått airdrop', value: 4000},
          { id: 7, title: 'Mining', value: 5000},
        ],
        choices: [1],
        chosenOptions: [
          { id: 1, title: 'kjøp og salg', value: 7000},
        ],
        form: {
          username: '',
          email: '',
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
        let transactionsValue = parseInt($(".current-value").text());
        this.form.transactions = transactionsValue;
        this.form.sum += transactionsValue;
      },
      submitData() {
        this.form.isLoading = true;

        let formData = new FormData();
        formData.append('username', this.form.username);
        formData.append('email', this.form.email);
        formData.append('transactions', this.form.transactions);

        this.chosenOptions.forEach((item) => {
          formData.append('choices[]', `${item.title} : ${item.value}`);
        });

        formData.append('sum', this.form.sum);

        // send request to contact form 7
        axios.post(`${this.baseUrl}/wp-json/contact-form-7/v1/contact-forms/${this.contactFormId}/feedback`, formData)
        .then((response) => {
          console.log(response);
          if (response.data.status === 'validation_failed') {
            this.form.isLoading = false;
            this.form.message.error = 'Ett eller flere felt har feil';
          } else {
            this.form.isLoading = false;
            this.form.message.success = 'Din forespørsel er sendt. Vi vil komme tilbake til deg så snart som mulig.';
          }
        })
        .catch(function (error) {
          console.log(error);
          this.form.isLoading = false;
        });
      }
    },
    watch: {
      choices(newChoice, old) {
        this.chosenOptions = [];
        this.form.sum = 0;
        for (let i = 0; i < this.choices.length; i++) {
          for(let j = 0; j < this.options.length; j++) {
            if (this.choices[i] === this.options[j].id) {
              this.chosenOptions.push(this.options[j]);
              this.form.sum += this.options[j].value;
              break;
            }
          }
        }
      }
    },
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
