import EmailVerificationController from './EmailVerificationController'
import SocialiteController from './SocialiteController'
const Auth = {
    EmailVerificationController: Object.assign(EmailVerificationController, EmailVerificationController),
SocialiteController: Object.assign(SocialiteController, SocialiteController),
}

export default Auth