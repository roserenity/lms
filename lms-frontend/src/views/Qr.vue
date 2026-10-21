<template>
    <section>
        <v-card class="custom-border border" :style="{'--bgColor': getPrimary}">
            <div app id="qr-reader" class="primary-font f12"></div>            
        </v-card>
        <v-snackbar
            v-model="snackbar"
            color="primary"
            timeout="-1"
            vertical
            bottom
            absolute
        >
            <div class="primary-font f12 mb-3">QR code details:</div>
            <div class="primary-font f14 fw500">{{ qrLink }}</div>

            <template v-slot:action="{ attrs }">
                <v-btn
                    text
                    @click="copyToClipboard"
                    dense
                    small
                >
                    Copy
                    <v-icon right small>
                        mdi-checkbox-multiple-blank-outline
                    </v-icon>
                </v-btn>
                <v-btn text small :href="qrLink" target="_blank"> 
                    Open link in a new tab 
                    <v-icon right small>
                        mdi-open-in-new
                    </v-icon>
                </v-btn>
                <v-btn
                    color="white"
                    icon
                    small
                    v-bind="attrs"
                    @click="snackbar = false"
                    dense
                >
                    <v-icon small>
                        mdi-close
                    </v-icon>
                </v-btn>
            </template>
        </v-snackbar>
        <v-snackbar
            v-model="snackbarError"
            color="red darken-2"
            timeout="-1"
            vertical
            bottom
            absolute
        >
            <div><b class="primary-font">{{ error }}</b></div>

            <template v-slot:action="{ attrs }">
                <v-btn
                    color="white"
                    icon
                    v-bind="attrs"
                    @click="snackbarError = false"
                    dense
                >
                    <v-icon>
                        mdi-close
                    </v-icon>
                </v-btn>
            </template>
        </v-snackbar>
    </section>
</template>

<style>
#qr-reader {
    border: 0px !important;
    padding: 40px !important;
}
#html5-qrcode-button-camera-permission, #html5-qrcode-button-camera-stop, #html5-qrcode-button-camera-start  {
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.04), 0px 0px 2px rgba(0, 0, 0, 0.06), 0px 0px 1px rgba(0, 0, 0, 0.04) !important;
    padding: 5px !important;
    border-radius: 5px !important;
    background: var(--bgColor) !important;
    color: white !important;
    padding-left: 10px !important;
    padding-right: 10px !important;
    margin-bottom: 10px !important;
}
#html5-qrcode-anchor-scan-type-change {
    text-decoration: none !important;
    color: var(--bgColor) !important;
}
#qr-reader__scan_region > video {
    width: 400px !important;
}
#qr-reader__scan_region {
    justify-content: center !important;
    align-items: center !important;
    display: flex !important;
    width: 400px !important;
    margin: 0 auto !important;
}

@media screen and (max-width: 400px) {
    #qr-reader__scan_region > video, #qr-reader__scan_region {
        width: 100% !important;
    }  
}
</style>

<script>
import { mapState, mapMutations } from "vuex";
import { Html5QrcodeScanner } from "html5-qrcode"
export default {
    data: () => ({
        snackbar: false,
        snackbarError: false,
        qrLink: '',
        error: '',
        html5QrcodeScanner: null
    }),
    mounted () {
        const config = {
            fps: 10,
            qrbox: { width: 300, height: 300 },
        };
        this.html5QrcodeScanner = new Html5QrcodeScanner('qr-reader', config);
        this.html5QrcodeScanner.render(this.onScanSuccess, this.onScanFailure);
    },
    methods: {
        ...mapMutations(['alertMutation']),

        onScanSuccess (decodedText, decodedResult) {
            this.snackbar = true
            this.snackbarError = false
            this.qrLink = decodedText
        },

        copyToClipboard() {
            navigator.clipboard.writeText(this.qrLink);
            this.alertMutation({
                show: true,
                text: 'Copied!',
                type: "success"
            })
        }
    },
    computed: {
        ...mapState({
            customization: (state) => state.customization
        }),
        getPrimary(){
            return this.customization.hasOwnProperty('primary_buttons_color') ? this.customization.primary_buttons_color : this.$vuetify.theme.currentTheme.primary
        },
    }
}
</script>