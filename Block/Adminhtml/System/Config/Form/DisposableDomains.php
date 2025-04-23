<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Security\Block\Adminhtml\System\Config\Form;

use Magefan\Community\Api\SecureHtmlRendererInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class DisposableDomains extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var SecureHtmlRendererInterface
     */
    private $mfSecureRenderer;

    /**
     * @param Context $context
     * @param SecureHtmlRendererInterface|null $mfSecureRenderer
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(
        Context $context,
        SecureHtmlRendererInterface $mfSecureRenderer = null,
        array $data = [],
        ?SecureHtmlRenderer $secureRenderer = null
    ) {
        $this->mfSecureRenderer = $mfSecureRenderer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(SecureHtmlRendererInterface::class);
        parent::__construct($context, $data, $secureRenderer);
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $textPart2 = 'kuandika.com, 10minutemail.com, andinews.com, ahaks.com, eluxeer.com, 2-ch.space, 33mail.com, 6paq.com, DingBone.com, FudgeRub.com,' .
            'LookUgly.com, SmellFear.com, a45.in, abyssmail.com, add3000.pp.ua, amaily.org, anappthat.com, anonymbox.com, anonymousemail.me,' .
            'anotherdomaincyka.tk, anonymous-email.me, anonymousmail.org, armyspy.com, asdasd.ru, axe.axeprim.eu, azazazatashkent.tk, bongobongo.cf,' .
            'bongobongo.ga, bongobongo.ml, box.yadavnaresh.com.np, bspamfree.org, burnermail.io, bund.us, bundes-li.ga, bspamfree.org, byom.de,' .
            'cachedot.net, ckaazaza.tk, clashmail.com, crazymailing.com, cuvox.de, dasdasdascyka.tk, dayrep.com, deadaddress.com, discard.email,' .
            'discardmail.com, discardmail.de, disposable.pingfu.net, disposableinbox.com, disposable-mail.com, dispostable.com, dripmail.org,' .
            'dropmail.me, dudmail.com, e4ward.com, easytrashmail.com, ebano.campano.cl, ee2.pl, eelmail.com, einrot.com, email-fake.com,' .
            'email.hideme.be, emailondeck.com, emkei.cz, eml.pp.ua, en.getairmail.com, eqeqeqeqe.tk, eyepaste.com, faketemporaryemail.com,' .
            'fake-email.pp.ua, fakeinbox.com, fakedemail.com, fakemailgenerator.com, fiifke.de, fleckens.hu, freeletter.me, freemail.ms,' .
            'freundin.ru, gaf.oseanografi.id, get.pp.ua, getairmail.com, gishpuppy.com, googdad.tk, grandmasmail.com, grr.la,' .
            'guerrillamail.biz, guerrillamail.com, guerrillamail.de, guerrillamail.net, guerrillamail.org, guerrillamailblock.com,' .
            'gustr.com, hartbot.de, hideme.be, hidemail.de, hmamail.com, hulapla.de, incognitoemail.org, inboxbear.com, inboxdesign.me,' .
            'inboxkitten.com, inboxstore.me, incognitomail.org, inmynetwork.cf, inmynetwork.ga, inmynetwork.gq, inmynetwork.ml, inmynetwork.tk,' .
            'instantlyemail.com, jetable.org, jobbikszimpatizans.hu, jourrapide.com, kurzepost.de, labetteraverouge.at, lazyinbox.com,' .
            'loadby.us, loh.pp.ua, lolitka.cf, lolitka.ga, lolitka.gq, lolito.tk, mail.australia.asia, mailcatch.com, maildrop.cc, maildim.com,' .
            'mailexpire.com, mailforspam.com, mailinator.com, mailinator.net, mailnesia.com, mailnull.com, mailsac.com, mailshell.com,' .
            'meltmail.com, mfsa.ru, mintemail.com, mmmmail.com, mmmail.com, moakt.com, msgos.com, mt2015.com, mt2016.com, my.vondata.com.ar,' .
            'mynetwork.cf, mytempemail.com, mytrashmail.com, no-spam.ws, nowemail.com, objectmail.com, odnorazovoe.ru, pfui.ru, poh.pp.ua,' .
            'postonline.me, proxymail.eu, protempmail.com, protectyourmail.com, q314.net, rcpt.at, recyclemail.dk, regspaces.tk, rhyta.com,' .
            's0ny.net, safetypost.de, schafmail.de, sdfghyj.tk, send-email.org, sharklasers.com, shitmail.me, showslow.de, spam.la, spam.su,' .
            'spam4.me, spambog.com, spambog.de, spambog.ru, spambox.us, spamex.com, spamfree24.org, spamfree24.com, spamfree24.de, spamfree24.info,' .
            'spamfree24.net, spamgourmet.com, spamobox.com, spamstack.net, spaml.com, squizzy.de, superrito.com, sweetxxx.de, tafmail.com,' .
            'techgroup.me, teewars.org, teleworm.us, temp-mail.org, temp-mail.ru, tempemail.net, tempemail.org, tempemail.pro, tempemail.us,' .
            'tempinbox.com, tempmailer.com, tempmail.de, tempmail.it, tempmail.pro, tempmail.us, tempmailaddress.com, tempmailbox.org,' .
            'tempsky.com, tempomail.fr, thisisnotmyrealemail.com, thismail.ru, thrma.com, throwawayemailaddress.com, throwawaymail.com,' .
            'tmail.ws, trash-mail.com, trash-mail.de, trash-mail.at, trashbox.eu, trashmail.at, trashmail.com, trashmail.me, trashmail.net,' .
            'trashmail.org, trashymail.com, trbvm.com, ts-by-tashkent.cf, ts-by-tashkent.ga, ts-by-tashkent.gq, ts-by-tashkent.ml,' .
            'ts-by-tashkent.tk, vaasfc4.tk, vickaentb.cf, vickaentb.ga, vickaentb.gq, vickaentb.ml, vickaentb.tk, vihost.ml, vihost.tk,' .
            'vmani.com, wasdfgh.cf, wasdfgh.ga, wasdfgh.gq, wasdfgh.ml, wasdfgh.tk, webuser.in, wegwerf-email-addressen.de, wegwerf-email.de,' .
            'wegwerf-email.net, wegwerf-email.org, wegwerfmail.de, wegwerfmail.net, wegwerfmail.org, wfgdfhj.tk, wh4f.org, wickmail.net,' .
            'wimsg.com, xy9ce.tk, yapped.net, yopmail.com, yopmail.fr, yopmail.net, zaktouni.frzeta-telecom.com, 0-mail.com, 0815.ru,' .
            '0clickemail.com, 0wnd.net, 0wnd.org, 20minutemail.com, 2prong.com, 30minutemail.com, 3d-painting.com, 4warding.com, 4warding.net,' .
            '4warding.org, 60minutemail.com, 675hosting.com, 675hosting.net, 675hosting.org, 6url.com, 75hosting.com, 75hosting.net,' .
            '75hosting.org, 7tags.com, 9ox.net, a-bc.net, afrobacon.com, ajaxapp.net, amilegit.com, amiri.net, amiriindustries.com, anonbox.net,' .
            'antichef.com, antichef.net, antispam.de, baxomale.ht.cx, beefmilk.com, binkmail.com, bio-muesli.net, bobmail.info, bodhi.lawlita.com,' .
            'bofthew.com, brefmail.com, broadbandninja.com, bsnow.net, bugmenot.com, bumpymail.com, casualdx.com, centermail.com, centermail.net,' .
            'chogmail.com, choicemail1.com, cool.fr.nf, correo.blogos.net, cosmorph.com, courriel.fr.nf, courrieltemporaire.com, cubiclink.com,' .
            'curryworld.de, cust.in, dacoolest.com, dandikmail.com, deadspam.com, despam.it, despammed.com, devnullmail.com, dfgh.net,' .
            'digitalsanctuary.com, discardmail.com, Disposableemailaddresses:emailmiser.com, disposableaddress.com, disposeamail.com,' .
            'disposemail.com, dm.w3internet.co.ukexample.com, dodgeit.com, dodgit.com, dodgit.org, donemail.ru, dontreg.com, dontsendmespam.de,' .
            'dump-email.info, dumpandjunk.com, dumpmail.de, dumpyemail.com, email60.com, emaildienst.de, emailias.com, emailigo.de, emailinfive.com,' .
            'emailmiser.com, emailsensei.com, emailtemporario.com.br, emailto.de, emailwarden.com, emailx.at.hm, emailxfer.com, emz.net,' .
            'enterto.com, ephemail.net, etranquil.com, etranquil.net, etranquil.org, explodemail.com, fakeinformation.com, fastacura.com,' .
            'fastchevy.com, fastchrysler.com, fastkawasaki.com, fastmazda.com, fastmitsubishi.com, fastnissan.com, fastsubaru.com, fastsuzuki.com,' .
            'fasttoyota.com, fastyamaha.com, filzmail.com, fizmail.com, fr33mail.info, frapmail.com, front14.org, fux0ringduh.com, garliclife.com,' .
            'get1mail.com, get2mail.fr, getonemail.com, getonemail.net, ghosttexter.de, girlsundertheinfluence.com, gowikibooks.com,' .
            'gowikicampus.com, gowikicars.com, gowikifilms.com, gowikigames.com, gowikimusic.com, gowikinetwork.com, gowikitravel.com,' .
            'gowikitv.com, great-host.in, greensloth.com, gsrv.co.uk, guerillamail.biz, guerillamail.com, guerillamail.net, guerillamail.org,' .
            'guerrillamail.biz, guerrillamail.com, h.mintemail.com, h8s.org, haltospam.com, hatespam.org, hidemail.de, hochsitze.com, hotpop.com,' .
            'ieatspam.eu, ieatspam.info, ihateyoualot.info, iheartspam.org, imails.info, inboxclean.com, inboxclean.org, incognitomail.com,' .
            'incognitomail.net, incognitomail.org, insorg-mail.info, ipoo.org, irish2me.com, iwi.net, jetable.com, jetable.fr.nf, jetable.net,' .
            'jetable.org, jnxjn.com, junk1e.com, kasmail.com, kaspop.com, keepmymail.com, killmail.com, killmail.net, kir.ch.tc, klassmaster.com,' .
            'klassmaster.net, klzlk.com, kulturbetrieb.info, letthemeatspam.com, lhsdv.com, lifebyfood.com, link2mail.net, litedrop.com,' .
            'lol.ovpn.to, lookugly.com, lopl.co.cc, lortemail.dk, lr78.com, m4ilweb.info, maboard.com, mail-temporaire.fr, mail.by,' .
            'mail.mezimages.net, mail2rss.org, mail333.com, mail4trash.com, mailbidon.com, mailblocks.com, maileater.com, mailfreeonline.com,' .
            'mailin8r.com, mailinater.com, mailinator.com, mailinator.net, mailinator2.com, mailincubator.com, mailme.ir, mailme.lv,' .
            'mailmetrash.com, mailmoat.com, mailnator.com, mailsiphon.com, mailslite.com, mailzilla.com, mailzilla.org, mbx.cc, mega.zik.dj,' .
            'meinspamschutz.de, messagebeamer.de, mierdamail.com, moburl.com, moncourrier.fr.nf, monemail.fr.nf, monmail.fr.nf, msa.minsmail.com,' .
            'mt2009.com, mx0.wwwnew.eu, mycleaninbox.net, mypartyclip.de, myphantomemail.com, myspaceinc.com, myspaceinc.net, myspaceinc.org,' .
            'myspacepimpedup.com, myspamless.com, mytrashmail.com, neomailbox.com, nepwk.com, nervmich.net, nervtmich.net, netmails.com,' .
            'netmails.net, netzidiot.de, neverbox.com, no-spam.ws, nobulk.com, noclickemail.com, nogmailspam.info, nomail.xl.cx, nomail2me.com,' .
            'nomorespamemails.com, nospam.ze.tc, nospam4.us, nospamfor.us, nospamthanks.info, notmailinator.com, nowmymail.com, nurfuerspam.de,' .
            'nus.edu.sg, nwldx.com, obobbo.com, oneoffemail.com, onewaymail.com, online.ms, oopi.org, ordinaryamerican.net, otherinbox.com,' .
            'ourklips.com, outlawspam.com, ovpn.to, owlpic.com, pancakemail.com, pimpedupmyspace.com, pjjkp.com, politikerclub.de, poofy.org,' .
            'pookmail.com, privacy.net, prtnx.com, punkass.com, PutThisInYourSpamDatabase.com, qq.com, quickinbox.com, recode.me, recursor.net,' .
            'regbypass.com, regbypass.comsafe-mail.net, rejectmail.com, rklips.com, rmqkr.net, rppkn.com, rtrtr.com, safe-mail.net, safersignup.de,' .
            'safetymail.info, sandelf.de, saynotospams.com, selfdestructingmail.com, SendSpamHere.com, shiftmail.com, shortmail.net, sibmail.com,' .
            'skeefmail.com, slaskpost.se, slopsbox.com, smellfear.com, snakemail.com, sneakemail.com, sofimail.com, sofort-mail.de, sogetthis.com,' .
            'soodonims.com, spamavert.com, spambob.com, spambob.net, spambob.org, spambog.ru, spambox.info, spambox.irishspringrealty.com,' .
            'spambox.us, spamcannon.com, spamcannon.net, spamcero.com, spamcon.org, spamcorptastic.com, spamcowboy.com, spamcowboy.net,' .
            'spamcowboy.org, spamday.com, spamfree24.eu, SpamHereLots.com, SpamHerePlease.com, spamhole.com, spamify.com, spaminator.de,' .
            'spamkill.info, spaml.de, spammotel.com, spamobox.com, spamoff.de, spamslicer.com, spamspot.com, spamthis.co.uk, spamthisplease.com,' .
            'spamtrail.com, speed.1s.fr, supergreatmail.com, supermailer.jp, suremail.info, teleworm.com, tempalias.com, tempe-mail.com,' .
            'tempemail.biz, tempemail.com, TempEMail.net, tempinbox.co.uk, tempmail.it, tempmail2.com, temporarily.de, temporarioemail.com.br,' .
            'temporaryemail.net, temporaryforwarding.com, temporaryinbox.com, thanksnospam.info, thankyou2010.com, tilien.com, tmailinator.com,' .
            'tradermail.info, trash-amil.com, trash2009.com, trashemail.de, trashmail.at, trashmail.de, trashmail.ws, trashmailer.com,' .
            'trashymail.net, trillianpro.com, turual.com, twinmail.de, tyldd.com, uggsrock.com, upliftnow.com, uplipht.com, venompen.com,' .
            'veryrealemail.com, viditag.com, viewcastmedia.com, viewcastmedia.net, viewcastmedia.org, webm4il.info, wegwerfadresse.de,' .
            'wegwerfemail.de, wegwerfmail.org, wetrainbayarea.com, wetrainbayarea.org, whyspam.me, willselfdestruct.com, winemaven.info,' .
            'wronghead.com, wuzup.net, wuzupmail.net, www.e4ward.com, www.gishpuppy.com, www.mailinator.com, wwwnew.eu, xagloo.com,' .
            'xemaps.com, xents.com, xmaily.com, xoxy.net, yep.it, yogamaven.com, ypmail.webarnak.fr.eu.org, yuurok.com, zehnminutenmail.de,' .
            'zippymail.info, zoaxe.com, zoemail.org';

        $html = '
            <p class="note">
               <span><b>' . __('If you want you can add additional disposable domains in this field, each in a new line.') . '</b></span><br>
               <span id="short-text">' . __('Extension comes with a wide range of disposable domains list: ') . '<a href="javascript:void(0);" id="view-list">' . __('View List') . '</a></span>
            </p>
        ';

        $script = '
           require(["jquery", "Magento_Ui/js/modal/alert", "domReady!"], function($, alert){
               $("#view-list").on("click", function(){
                alert({
                    title: "' . __('Fraudulent Domains list') . '",
                    content: "' . $textPart2 . '",
                    modalClass: "fraudulent-domains"
                  });
               })
            });
        ';

        $html .= $this->mfSecureRenderer->renderTag('script', [], $script, false);

        $element->setComment($html);
        return parent::render($element);
    }
}
