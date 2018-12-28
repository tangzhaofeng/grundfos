/*var app = new Vue({
	el: '#app',
	data: {
		message: 'Hello Vue!'
	},
	methods: {
		
	}
		
})*/

console.log(languageObj);

var messages = {
	en: {
		message: {
			slogan:'TAG LINE HERE',
			videos:'Videos',
			documents: 'Documents',
			webinars: 'Webinars',
			FAQs: 'FAQs',
			feedback: 'Feedback',
			certificate:'Certificate',
			feedbackTitle: 'Do you think the training video are helpful?',
			yes: 'Yes',
			average: 'Average',
			no: 'No',
			submitBtn: "Submit",
			sign:"Sign Up",
			more:'More',
			less:'Less',
			guide:{
				videoInfo:'You can view the videos in the Videos section.',
				certificateInfo:'A certificate will be awarded once you have completed all the videos.',
				documentsInfo:'Useful documents can be found in the Documents section.',
				webinarsInfo:'You can sign up for the events in the Webinars section.',
				FAQSInfo:'FAQs section shall be able to answer some of your questions about DUO platform.',
				feedbackInfo:'You are welcome to provide us your feedback about DUO platform.',
				next:'Next',
				previous:'Previous',
				study:'Study'
			},
			introduction:'Introduction',
			home:"Home",
			nameTip:'姓名填写错误',
			companyTip:'公司名称填写错误',
			positionTip:'公司职务填写错误',
			eMailTip:'邮箱填写格式有误',
			telephoneTip:'电话填写错误',
			submitSuccess:"提交成功",
			comingSoon:"敬请期待"
		}
	},
	zh: {
		message: {
			slogan:'标语',
			videos: '视频',
			documents: '文档',
			webinars: '活动报名',
			FAQs: '常见问题',
			feedback: '反馈',
			certificate:'证书',
			feedbackTitle: '您觉得这些内容对你有帮助吗？',
			yes: '有',
			average: '一般',
			no: '没有',
			submitBtn: "提交",
			sign:"报名",
			more:'展开',
			less:'收起',
			guide:{
				videoInfo:'您可以在视频区内观看学习你所感兴趣的内容',
				certificateInfo:'观看学习完四个视频，即可获取证书',
				documentsInfo:'您可以在帮助文档区内阅读学习你所感兴趣的帮助文档',
				webinarsInfo:'您可以在活动区内报名你所感兴趣的活动',
				FAQSInfo:'您可以在FAQ区内查看您所遇到的问题解答',
				feedbackInfo:'您可以对这网站进行评价',
				next:'下一步',
				previous:'上一步',
				study:'学习'
			},
			introduction:'活动介绍',
			home:"主页",
			nameTip:'姓名填写错误',
			companyTip:'公司名称填写错误',
			positionTip:'公司职务填写错误',
			eMailTip:'邮箱填写格式有误',
			telephoneTip:'电话填写错误',
			submitSuccess:"提交成功",
			comingSoon:"敬请期待"
		}
	}
}

var i18n = new VueI18n({
	locale: 'en',
	messages:messages
})

var app = new Vue({
	el: '#app',
	i18n:i18n,
	data: {
		message: 'Hello Vue!',
		language:languageObj.type,
	},
	mounted:function(){
		this.change(this);
	},
	methods: {
		change: function(that) {
			that.$i18n.locale = languageObj.Change
		}
	}
})