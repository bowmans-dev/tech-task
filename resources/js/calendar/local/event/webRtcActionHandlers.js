import sendOffer from './webRTC/sendOffer.js';
import receiveOffer from './webRTC/receiveOffer.js';
import receiveAnswer from './webRTC/receiveAnswer.js';
import iceCandidate from './webRTC/iceCandidate.js';

export const webRtcActionHandlers = {
  send_offer: sendOffer,
  receive_offer: receiveOffer,
  receive_answer: receiveAnswer,
  ice_candidate: iceCandidate,
};