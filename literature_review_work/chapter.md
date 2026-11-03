CHAPTER 2
LITERATURE REVIEW

## 2.1 Introduction

This chapter reviews the literature relevant to the design and development of a scalable location-based online grocery and daily-essential delivery system for Batticaloa District, Sri Lanka, using DailyCart as the case study. The central problem concerns how customers, independent vendors and delivery riders can participate in a coordinated digital service that provides reliable product information, understandable ordering, appropriate payment options and visible delivery progress. The review connects this practical problem with research on technology acceptance, electronic service quality, grocery fulfilment and information-system design.

DailyCart combines a web application with customer, vendor and rider mobile interfaces supported by a shared application programming interface. Its scope includes storefront discovery, vendor-specific orders, inventory, scheduled delivery, recurring purchases, payments, refunds and communication between participants. These functions establish the boundaries of the review. Broader topics such as nationwide logistics, artificial-intelligence recommendations and automated route optimisation are not treated as research contributions of the present system.

The chapter develops two complementary perspectives. The first concerns whether consumers perceive the service as useful, usable, trustworthy and accessible. The second concerns whether the software can coordinate its transactions and delivery activities reliably while supporting future growth. The distinction matters because willingness to adopt a service is not evidence that its inventory, payment or delivery processes operate correctly. Equally, technically correct software does not establish that consumers consider it appropriate for their shopping practices.

The discussion progresses from grocery-specific requirements to platform coordination, location, inventory, scheduling, architecture, trust and acceptance. It then examines Sri Lankan evidence and relevant existing services, identifies limitations in the reviewed knowledge, and develops the conceptual foundation for DailyCart. Throughout the chapter, proposed design implications are distinguished from empirical findings and from outcomes that must be established through the study's own evaluation.

## 2.2 Scope and approach of the review

This chapter uses a focused narrative review organised around the research title and objectives. Sources include foundational information-systems studies, empirical online-grocery research, research syntheses, official technical guidance and public descriptions of relevant delivery platforms. Academic sources explain relationships and competing approaches; technical documentation clarifies implementation mechanisms; platform websites establish publicly described service capabilities. These categories provide different kinds of evidence and are not treated as interchangeable.

The source selection concentrates on online grocery adoption, multi-sided platforms, attended home delivery, location privacy, electronic service quality and scalable application design. Searches also considered Sri Lankan online-grocery studies and official statistics on digital access. Sources were retained where they contributed to a defined research construct, a core system requirement or an evaluation criterion. Foundational studies remain relevant where they establish the concepts used in the investigation, while recent work helps assess whether those concepts adequately describe contemporary grocery services.

The review is not presented as an exhaustive systematic review or a meta-analysis. No comprehensive database-screening count or pooled effect estimate is claimed. Public platform information was considered as available on 31 October 2026. Such information can change and does not independently establish transaction quality, service-area coverage at every address or internal architecture. Where a capability is not verified, it is treated as unknown rather than absent.

## 2.3 Online grocery and daily-essential delivery systems

Online grocery services differ from a generic online catalogue because purchasing is connected to household replenishment, product availability and physical fulfilment. A customer may successfully submit an order yet receive little practical benefit if an essential item is unavailable or delivery occurs at an unsuitable time. Daily essentials also include non-perishable household products, so the system must accommodate different product characteristics without assuming that all items have identical freshness or storage requirements.

Tyrväinen and Karjaluoto (2022) synthesised 50 independent samples comprising 20,538 respondents in a meta-analysis of online-grocery shopping before and during COVID-19. Their findings identify perceived usefulness and attitude as important mechanisms connecting antecedents with purchase intention, while also showing that the pandemic context changed the importance of determinants. This supports attention to perceived shopping benefits, but limits straightforward transfer of emergency-period behaviour to ordinary household purchasing.

Malekpour et al. (2026) reviewed 40 quantitative studies and organised 49 attributes associated with online-grocery service experiences. Their synthesis extends attention beyond website interaction to fulfilment, information, security, customer circumstances and perceived risks. Its breadth supports treating grocery delivery as a connected service. However, weight analysis identifies consistency in reported relationships rather than establishing a universal causal ranking that can be applied unchanged to Batticaloa.

For DailyCart, the resulting design implication is to examine the entire purchasing sequence: finding a store or product, checking availability, creating a basket, understanding charges, paying, preparing the order and receiving the delivery. Each stage produces information needed by the next participant. For example, a customer-facing stock indicator depends on vendor updates, while a delivery estimate depends on preparation and travel. An evaluation restricted to the visual appearance of the customer interface would therefore omit important parts of the research problem.

Convenience should also be interpreted as a potential benefit rather than an automatic consequence of digitisation. A complicated checkout, uncertain stock position or repeated telephone confirmation may transfer work to the customer instead of reducing it. Consequently, the literature provides a rationale for investigating time, travel and effort savings, while the local study must determine which benefits respondents actually value.

## 2.4 Multi-vendor platforms and stakeholder coordination

Hagiu and Wright (2015) distinguish multi-sided platforms from resellers and vertically integrated organisations through the relationships between participating groups and the control retained over their interactions. Their analysis helps explain why a platform connecting independent grocery vendors involves different coordination problems from a supermarket operating its own online store. The framework is economic and organisational; it does not prescribe a specific grocery database or delivery algorithm.

The distinction is relevant to DailyCart because vendors maintain their own product information and participate in order preparation, while the platform coordinates customer access and delivery workflows. An independent vendor's delay or inaccurate stock record can affect the customer's experience of the entire platform. The design must therefore establish who can change product availability, confirm an order, assign a rider and resolve a disputed transaction. These responsibilities form part of the service model, not simply the navigation structure of separate dashboards.

The current checkout design creates vendor-specific orders from a basket containing products from more than one vendor. This makes the difference between a customer purchase and a vendor fulfilment task explicit. A shared checkout can reduce repeated customer entry, but it introduces questions about delivery-charge allocation, separate preparation progress, cancellation and refunds. The literature on platform organisation provides a useful basis for identifying these issues; their resolution remains a design decision that requires functional testing and clear customer communication.

Storefront discovery also extends the platform beyond a single combined product list. Customers can identify the seller associated with an item, while vendors can maintain a recognisable presence. For the study, the relevant question is whether this information helps customers complete grocery tasks and understand responsibility for an order. Store branding, following and promotional banners are supporting functions rather than independent research objectives.

The stakeholder perspective further implies that customer acceptance alone cannot establish operational suitability. Vendor tasks include maintaining inventory and confirming preparation; rider tasks include identifying assignments, recording progress and completing delivery evidence. Administrators need oversight without exposing unrelated customer or vendor information unnecessarily. User evaluation should consequently include representative operational tasks, even where the questionnaire model primarily examines consumer adoption.

## 2.5 Location-based services and regional accessibility

### 2.5.1 Location information and service availability

Location-based grocery delivery links digital ordering to an address that can be served by participating vendors and riders. An address is therefore both a customer record and an operational input. DailyCart's location functions include geographic coordinates, mapping support and delivery-pricing configuration. Their research relevance lies in whether the system communicates coverage and delivery conditions accurately, rather than merely whether a map can be displayed.

The current pricing design accommodates geographic rules at zone, district, province and default levels. This supports a configurable approach to future expansion, but the availability of a configuration option does not demonstrate that an area is operationally served. For the Batticaloa study, service coverage must be defined independently of software capability. Delivery-zone decisions require information about participating vendors, rider availability and the addresses included in the evaluation.

Distance-related charges also need clear interpretation. A computed distance can support a fee estimate, but it is not by itself evidence of a reliable arrival time. Preparation, road conditions and the availability of a rider can affect fulfilment. The system should distinguish a selected delivery time, an estimated duration and an observed completion time so that customers and evaluators do not treat these different quantities as equivalent.

### 2.5.2 Location privacy and practical accessibility

Xu et al. (2005) investigate adoption of location-based services through trust and perceived privacy risk. Their results indicate that provider interventions can strengthen trust and reduce perceived risk. The study establishes that location functionality has a behavioural dimension, although its technological context differs from a contemporary grocery application. Its relevance is the need to consider how users understand and control location disclosure, rather than to assume that every historical intervention transfers directly.

For DailyCart, the design implication is purpose-related access to location information. A rider needs sufficient information to complete an assigned delivery; this does not imply unrestricted access to all customer addresses. Similarly, retaining delivery evidence requires a clear operational purpose. These considerations connect location handling to access control and trust without turning the study into a separate investigation of surveillance technologies.

Regional accessibility is defined here as the practical ability to use the service in its intended geographical and technological setting. It includes delivery coverage, device and connectivity conditions, understandable address entry and usable payment choices. It is distinct from perceived ease of use: a customer may understand the application but still live outside its service area. This distinction supports retaining regional accessibility as a separate construct in the DailyCart framework.

## 2.6 Product information inventory and transaction consistency

Product availability has both perceptual and technical dimensions. Customers need confidence that an advertised item can be supplied, while vendors need mechanisms for maintaining quantities and variants. A product name alone may be insufficient where size, weight or packaging changes the item being purchased. In DailyCart, product variants and vendor inventory therefore support the accuracy of the ordering process rather than merely enriching catalogue presentation.

Oracle's MySQL documentation explains how locking reads can protect records during transactions that depend on reading and subsequently changing shared data (Oracle, n.d.). This is relevant when several customers attempt to purchase the remaining stock of the same product. A check followed by an unprotected update can allow competing requests to act on the same earlier value. Locking and transactional updates provide mechanisms for coordinating these operations, although they also introduce contention that must be managed.

The design implication for DailyCart is that stock validation, order creation and relevant financial updates should be evaluated together. A failed checkout should not leave stock deducted without a corresponding order. Conversely, a successful order should not promise quantities that the transaction has not secured. These requirements can be investigated through cases involving insufficient stock, multiple vendors and simultaneous requests. Their inclusion follows from the system's shared inventory problem; it is not a claim that database transactions eliminate every form of stock inaccuracy.

Physical and digital inventory can still diverge when a vendor sells products through another channel or delays an update. The application can enforce internal rules but cannot independently verify every shelf quantity. This limitation makes vendor usability and working practices relevant to product availability. It also explains why the questionnaire should measure perceived availability separately from technical tests of stock consistency.

A further distinction concerns order history. An order should preserve the purchased product description and price sufficiently to explain the transaction even if a vendor later changes the catalogue. For the present study, this is a design implication of traceability and dispute handling. Detailed schemas belong in the design chapter, while the literature review establishes why stable transaction information matters to reliable grocery fulfilment.

## 2.7 Delivery scheduling recurring orders and fulfilment

### 2.7.1 Preparation and delivery scheduling

Agatz et al. (2011) examine time-slot management in attended home delivery and the need to balance customer service with operational considerations. Their work concerns the selection of delivery slots across a service region and demonstrates that delivery promises are linked to planning decisions. Its relevance to DailyCart is the relationship between customer flexibility and fulfilment feasibility, rather than the direct adoption of an optimisation model.

DailyCart applies a minimum preparation-time rule before a scheduled delivery can be selected. The current value of 30 minutes is a project-specific rule, not a universal duration established by the literature. Its suitability depends on factors such as basket size, vendor readiness and travel. The evaluation should therefore determine whether users understand the rule and whether the prototype enforces it consistently. Claims about real delivery punctuality require operational evidence beyond successful validation of the time field.

The order and delivery processes should also remain distinguishable. Vendor confirmation, packing, assignment, pickup and delivery completion represent different responsibilities. Recording these transitions can make delays easier to locate and explain. However, status visibility is useful only when the information is timely and accurate; a frequently refreshed screen does not establish that the underlying event has occurred.

### 2.7.2 Recurring purchasing and subscription models

Wagner et al. (2021) analyse paid delivery subscriptions using a grocery retailer's transaction data and matching and difference-in-differences methods. Subscribers ordered more frequently and spent more per month, but changes in slot preferences and operating costs reduced incremental profitability in the studied setting. This provides a counterpoint to the assumption that greater purchasing frequency necessarily improves a delivery service's economic performance.

Their subscription model must be distinguished from DailyCart's recurring product orders. A delivery membership provides delivery benefits over a period; recurring ordering schedules the repeated purchase of selected goods. Findings about membership profitability therefore cannot be used as direct evidence that DailyCart's recurring orders will save costs or increase loyalty. The transferable lesson is that repeated demand affects fulfilment requirements as well as customer convenience.

DailyCart's recurring-order mechanism checks eligible subscriptions and dispatches background work to generate orders. The relevant design questions include whether stock is available, whether a subscription is still active and whether failures are communicated. Customers also need understandable controls for pausing, resuming or cancelling future purchases. These are requirements to evaluate, not proof that automated replenishment is appropriate for every household.

### 2.7.3 Delivery accountability and service recovery

Delivery evidence, rider updates and customer notifications help connect the digital order with its physical completion. For DailyCart, proof of delivery and recorded status changes can support investigation when a customer disputes completion. Such records require appropriate access and interpretation: an uploaded image alone cannot establish every aspect of product condition or recipient satisfaction.

Failure handling is equally important. A cancelled or failed delivery can affect payment, inventory and customer communication. The research should examine whether the relevant participants receive consistent information and whether recovery actions leave the transaction in a comprehensible state. A system that handles only the successful path would provide incomplete evidence of reliability in a grocery context.

## 2.8 Architecture and scalability of web and mobile services

### 2.8.1 Shared services and architectural boundaries

Fielding (2000) develops an architectural account of network-based software and the REST style, including constraints intended to support properties such as independent evolution and scalable interaction. This provides a conceptual basis for separating client interfaces from shared server responsibilities. It does not imply that any HTTP interface automatically satisfies REST constraints or achieves good performance.

DailyCart uses a common backend for web and mobile workflows. The design rationale is to keep rules for stock, payment and order progression consistent across interfaces. A vendor updating an order through a mobile client should not encounter a different business rule from the corresponding web operation. Shared services can reduce duplicated logic, but they also create a central dependency whose failure may affect several user groups simultaneously.

The present application is best discussed as a modular application with service classes and shared data, rather than described as a deployed microservices system. For the scale of the study, a single coordinated application can simplify transactional consistency and testing. Dividing it into separately deployed services would introduce further questions about communication failure and distributed transactions. The review therefore supports evaluating architectural suitability for the defined problem rather than assuming that a more complex deployment is inherently superior.

### 2.8.2 Background processing and workload management

Microsoft's queue-based load-levelling guidance describes queues as buffers between incoming work and processing services, reducing the immediate impact of intermittent demand peaks (Microsoft, n.d.-b). Its competing-consumers guidance explains how workers can share queued work and emphasises issues such as ordering and repeat processing (Microsoft, n.d.-a). These are architectural patterns, not empirical evidence of DailyCart's capacity.

For DailyCart, notifications and recurring-order generation are relevant background tasks. Separating these tasks from an interactive checkout can reduce dependence on an external communication service during the customer's request. However, a queued task is not a completed task. Evaluation must consider worker availability, failed jobs, queue delay and the risk of applying the same operation more than once. For example, a retry should not create an unintended duplicate recurring order or financial effect.

Pagination and processing records in bounded batches are additional features of the current implementation. Their purpose is to avoid making each request or scheduled operation handle an indefinitely growing dataset. They provide design support for growth, but their effectiveness depends on query structure, indexes, workload and infrastructure. A source-code inspection can identify these mechanisms without establishing their measured benefit.

### 2.8.3 Technical growth and geographical expansion

Scalability has two relevant meanings in this study. Technical scalability concerns changes in response time, throughput and resource use as workload increases. Configurational expansion concerns adding vendors, products or service areas without redesigning core workflows. Geographic pricing rules support the second meaning; queues and bounded data access may support the first. Neither substitutes for evidence about the other.

The word scalable in the research title should consequently be supported by explicit evaluation conditions. Useful scenarios include increasing concurrent product searches, simultaneous checkouts and larger order histories. Results should report the test environment, dataset size, request mix, response-time distribution and error rate. A prototype may demonstrate a suitable expansion structure while still having a measured workload limit. Reporting that limit would strengthen the study's architectural argument rather than undermine it.

## 2.9 Trust security payments and service quality

### 2.9.1 Trust and controlled access

Gefen et al. (2003) integrate trust with technology acceptance in online shopping, showing the importance of considering confidence in the seller alongside assessments of the technology. This is relevant to a grocery platform where customers depend on several participants. The study's online-shopping setting supports the conceptual relationship, but does not establish that any individual DailyCart security feature will cause an increase in adoption.

Trust in DailyCart can involve the platform, vendor information, payment records and delivery actions. The practical design implications include identifiable sellers, clear transaction status and restricted access to personal information. Perceived security and actual protection should nevertheless be evaluated separately. A reassuring interface can coexist with weak authorisation, while strong controls can be poorly understood by users.

OWASP (2023) identifies broken object-level authorisation as a major API risk and recommends checks whenever an operation accesses a record identified by client input. For a multi-vendor delivery system, logging in and having a recognised role are not sufficient if a user can access another participant's order. This guidance supports testing ownership and assignment boundaries for customers, vendors and riders, rather than relying only on the visibility of interface menus.

### 2.9.2 Payment transparency and recovery

PayHere's Checkout API documentation describes payment-status notifications delivered through server callbacks with verification data (PayHere, n.d.). This distinguishes a verified payment notification from a customer's browser returning to a success page. The distinction is relevant to DailyCart's payment state, although successful integration testing must be reported separately from the presence of the integration code.

The current system also includes cash on delivery, wallet transactions and bank-transfer verification. These methods create different confirmation points. A submitted transfer slip is evidence offered for review, not automatic confirmation that money has been received. A wallet payment requires a consistent balance update, while cash collection is linked to delivery. The research implication is to investigate whether customers and operators can distinguish pending, verified, failed and refunded states.

Refunds and support extend the service beyond checkout. Parasuraman et al. (2005) distinguish ordinary electronic service quality from recovery encounters through E-S-QUAL and E-RecS-QUAL. The former includes efficiency, fulfilment, system availability and privacy; the latter addresses responsiveness, compensation and contact. This distinction supports treating a refund request or failed delivery as a legitimate evaluation scenario rather than an exceptional event outside the system's purpose.

For DailyCart, notifications, support tickets and recorded refund decisions can make recovery more transparent. Loyalty points, promotions, reviews and rider ratings are supporting customer-experience mechanisms. Their existence does not establish satisfaction or retention, and they do not require separate independent variables in the approved research model. The study should first establish whether core ordering and recovery tasks work reliably before interpreting additional engagement features as benefits.

## 2.10 Theoretical foundations for user acceptance

### 2.10.1 Technology Acceptance Model

Davis (1989) develops and validates measures of perceived usefulness and perceived ease of use, demonstrating their relationship with user acceptance. The work provides a foundation for investigating whether a technology is considered beneficial and manageable. For DailyCart, the corresponding concerns are whether grocery purchasing requires less effort overall and whether the interface is understandable.

Convenience is related to usefulness but is not identical to the full original construct. Likewise, perceived usability in the questionnaire should not be treated as a replacement for observed task performance. The study adapts these ideas to grocery purchasing and must define its measures accordingly. A respondent's favourable opinion of online shopping does not establish that they can complete a specific checkout unaided.

### 2.10.2 UTAUT and consumer use

Venkatesh et al. (2003) integrate earlier acceptance models in UTAUT, emphasising performance expectancy, effort expectancy, social influence and facilitating conditions. This broadens attention from an individual's interaction with a screen to the conditions that enable use. It provides a conceptual basis for considering practical access alongside perceived benefit in DailyCart.

Venkatesh et al. (2012) extend the model to consumer technology through UTAUT2, adding hedonic motivation, price value and habit. Their study used a two-stage survey of 1,512 mobile Internet consumers. Its consumer orientation is useful, but mobile Internet use and grocery delivery are different activities. The present study therefore uses selected theoretical insights rather than claiming to test the complete UTAUT2 model.

Delivery charges and repeated ordering may be interpreted through value and habitual-use perspectives, but neither requires adding a new variable merely because a corresponding feature exists. The six constructs already specified for DailyCart provide an achievable research model. Additional influences can be discussed as contextual considerations or future research where they are not measured directly.

### 2.10.3 Diffusion of Innovations

Rogers (2003) explains diffusion through communication and adoption within a social system. The framework draws attention to relative advantage, compatibility, complexity, trialability and observability. For DailyCart, these ideas suggest examining whether the service fits household routines and whether its benefits can be understood through experience. This contextual interpretation complements the acceptance models without establishing a separate diffusion model for the study.

The existence of reviews or store-following functions should not be interpreted as proof that community diffusion will occur. Such a claim would require evidence over time about communication and adoption. In this chapter, diffusion theory supports discussion of local fit and exposure, while the empirical framework remains centred on the specified consumer constructs.

## 2.11 Sri Lankan evidence and the Batticaloa context

Wijeratne and Kuruppu (2022) investigated post-pandemic online-grocery purchase intention in Sri Lanka using questionnaire data from 200 respondents and confirmatory factor analysis and structural equation modelling. Their reported results support positive associations of perceived usefulness and ease of use with purchase intention, while perceived risk was not statistically significant. This illustrates why the importance of a theoretically plausible factor should be tested rather than presumed. The contextual and survey-based design does not establish the performance of a grocery application in Batticaloa.

The Department of Census and Statistics (2024) reports a national digital-literacy rate of 65.0% and computer-literacy rate of 35.9% among people aged 5–69 in its 2024 annual bulletin. It also reports higher literacy in the urban sector and desktop or laptop ownership in 19.1% of households. These figures describe national conditions in that reporting period, not current adoption rates or Batticaloa-specific service demand. They nevertheless support avoiding an assumption that access to and familiarity with digital tools are uniform.

For the local investigation, district relevance must come from evidence about intended users and service conditions. The study should examine actual shopping difficulties, available ordering alternatives, payment preferences and the addresses that participating vendors and riders can serve. It should not infer that every Batticaloa household has the same connectivity, transport constraints or attitude toward delivery.

The proposed convenience sample of approximately 100 respondents can inform the prototype and identify associations within the sample. It cannot be treated as a probability estimate of the entire district. Questionnaire findings should therefore be interpreted alongside the sampling method and respondent characteristics. Similarly, successful demonstrations with sample products should not be reported as evidence of sustained commercial operations.

The practical contribution lies in connecting local evidence to design decisions. For example, difficulty understanding an address form can motivate a revised entry process, while concerns about payment confirmation can motivate clearer transaction information. This connection is stronger than treating Batticaloa only as a location label attached to a generic application.

## 2.12 Review of relevant existing platforms

### 2.12.1 Local marketplace and delivery services

KKY Bazar presents itself publicly as a Sri Lankan local marketplace. Its developer's case study describes a marketplace focused on Kattankudy commerce and a transition from informal messaging-based transactions to a vendor platform (KKY Bazar, n.d.; Velam.ai, n.d.). These descriptions establish a locally relevant comparison, but do not independently verify its full grocery fulfilment process or current recurring-order capabilities.

Savari's public service material identifies Batticaloa and describes a multi-vendor delivery environment including grocery and other categories (Savari Delivery Service, n.d.). This makes it a relevant local comparator. However, publicly visible marketing and framework descriptions should not be interpreted as evidence that every listed function is operational at every address. Its detailed performance, internal inventory controls and subscription behaviour remain outside what those descriptions establish.

These examples are sufficient to reject a research argument based on the presumed absence of local digital commerce. DailyCart's value must instead be investigated through requirements, usability and reliable execution of its defined workflows. The existence of alternatives creates a need for careful comparison rather than a reason to assume either that the research is unnecessary or that competitors lack important functions.

### 2.12.2 National grocery-service benchmarks

PickMe Market publicly describes grocery and essential-item delivery, merchant participation, multiple payment options and live tracking. Its service page states that delivery time depends on preparation, distance and traffic and, at the review date, that scheduling an order for later is unavailable (PickMe, n.d.). This provides a dated feature comparison, not a permanent claim about the service or confirmation of availability at every Batticaloa address.

Cargills Online presents a grocery-shopping interface with product availability indicators, customer addresses, order history, offers and rewards (Cargills Online, n.d.). It is useful as a retailer-operated benchmark for structured grocery purchasing. DailyCart's independent-vendor model creates different coordination responsibilities, so comparing the number of visible features alone would overlook the organisational distinction.

### 2.12.3 Comparative interpretation

Table 2.1 summarises the comparison using the scope that public evidence can support. DailyCart's entry describes the system under study rather than an independently evaluated commercial service. Unknown capabilities are not treated as absent.

[[PLATFORM_TABLE]]

The comparison shows that ordering, vendor participation and delivery visibility already exist in relevant service models. Consequently, the research contribution cannot rest on claiming those capabilities individually as inventions. DailyCart provides a bounded case in which a particular set of requirements can be implemented and evaluated transparently. Its combination of features is a design configuration to investigate, not sufficient evidence of novelty or superiority.

## 2.13 Critical synthesis of the reviewed evidence

The reviewed literature contributes different forms of knowledge. Acceptance research explains beliefs and intentions; service-quality research examines customer experience; operations research studies fulfilment decisions; architectural sources describe mechanisms and trade-offs. A complete account of DailyCart requires these perspectives to be connected without treating one type of evidence as proof of another. Table 2.2 summarises selected contributions and their boundaries.

[[EVIDENCE_TABLE]]

Several limitations emerge from this synthesis. First, purchase intention is often the outcome of interest, whereas the present project also asks whether real tasks can be completed. Second, aggregate findings and established models provide a starting point but do not determine the importance of each factor in a particular district. Third, platform descriptions expose customer-facing capabilities more readily than internal consistency, failure recovery or capacity limits.

Fourth, an integrated software artefact creates relationships between requirements that separate feature descriptions can conceal. A recurring order depends on stock and payment conditions; an authorised rider update changes information seen by the customer; a refund affects transaction history. These dependencies make cross-workflow evaluation more informative than a checklist recording whether each screen exists.

Finally, the reviewed evidence does not establish that the exact DailyCart configuration is the best solution for Batticaloa. It provides reasons for the design choices and questions against which they can be evaluated. The study must preserve that distinction when moving from the literature review to system development and results.

## 2.14 Research gap and contribution of DailyCart

The research gap is framed as an unresolved local design-and-evaluation question. The sources reviewed here do not establish how the specified combination of independent-vendor ordering, stock visibility, scheduled and recurring purchases, payment verification and coordinated delivery performs for the intended Batticaloa users. This is a bounded statement about the evidence considered, rather than a claim that no relevant research or commercial service exists.

The first component is contextual: the study investigates the needs and acceptance factors of its intended local users. The second is integrative: it translates those findings into connected customer, vendor and rider workflows. The third is evaluative: it examines functional correctness and usability alongside the architectural provisions and measured conditions relevant to future growth.

Hevner et al. (2004) explain the relationship between building information-system artefacts and evaluating their contribution to a problem domain. Their design-science perspective supports the argument that constructing software must be accompanied by evidence of utility and appropriate evaluation. It does not require the study to claim an entirely new acceptance theory or algorithm. DailyCart can contribute context-specific design knowledge by documenting the requirements, decisions, observed outcomes and limits of its artefact.

Accordingly, the study investigates how a location-based, multi-vendor online grocery and daily-essential delivery system can be designed and evaluated for Batticaloa District while considering convenience, usability, trust and security, delivery quality, product availability and regional accessibility. The architectural component concerns maintaining coherent shared services and supporting growth without assuming that commercial scalability has already been demonstrated.

## 2.15 Conceptual framework and relationship to system requirements

### 2.15.1 Consumer acceptance framework

The conceptual framework retains six explanatory constructs associated with adoption intention. Convenience concerns perceived reductions in shopping time, travel and effort. System usability concerns the perceived ease of learning and completing relevant activities. Trust and security concern confidence in participants and the handling of personal and transactional information. Delivery quality concerns perceived reliability, timeliness, accuracy and visibility. Product availability concerns the perceived accuracy and dependability of stock information. Regional accessibility concerns practical access under local geographical and technological conditions.

Adoption intention is the willingness to use DailyCart for grocery and daily-essential purchasing. It should not be used interchangeably with actual repeated use or satisfaction after extended operation. If respondents have not used the prototype, their responses represent expectations. Post-task acceptance and observed performance should be labelled separately when participants interact with the system.

Figure 2.1 presents the proposed relationships. Each construct has a separate hypothesised association with adoption intention; the arrows do not represent established causality or a sequence through which the constructs must pass.

[[FRAMEWORK_FIGURE]]

The framework is informed by the acceptance, trust, service-quality and location perspectives discussed earlier. It is an adapted framework for this study rather than a claim to validate TAM, UTAUT or E-S-QUAL in their entirety. The questionnaire must use clearly defined items and assess whether related constructs, especially convenience and usability, remain sufficiently distinct in the collected data.

The proposed associations are H1, convenience and adoption intention; H2, system usability and adoption intention; H3, trust and security and adoption intention; H4, delivery quality and adoption intention; H5, product availability and adoption intention; and H6, regional accessibility and adoption intention. Each association is expected to be positive, but its direction and statistical support remain empirical questions. Cross-sectional associations alone will not establish causation.

### 2.15.2 Translation into requirements

Table 2.3 links the constructs to the current system scope. The mapping explains why the functions are relevant; it does not assume that adding a feature necessarily improves its associated construct.

[[CONSTRUCT_TABLE]]

Supporting functions should remain proportionate to the research objective. Reviews, loyalty, promotions, financial reporting and support contribute to the surrounding service, but they do not redefine DailyCart as a general marketing or financial-management study. The main investigation remains the design and evaluation of grocery ordering and delivery for the specified setting.

## 2.16 Evaluation implications from the literature

ISO 9241-11 defines usability in relation to effectiveness, efficiency and satisfaction for specified users, goals and contexts (International Organization for Standardization, 2018). This supports task-based evaluation alongside the questionnaire. Relevant DailyCart tasks include finding an item, selecting a valid delivery time, recognising payment status, updating vendor stock and completing a rider assignment. Measuring completion, errors and assistance provides evidence that differs from general statements about liking the application.

Functional evaluation should follow the dependencies identified in the review. Representative cases include multi-vendor checkout, insufficient stock, bank-transfer approval or rejection, unauthorised record access, subscription failure and refund processing. Tests should check the resulting records and visible states, not only whether a request returns a success message. This provides a basis for discussing reliability within the tested conditions.

Performance evaluation should define its workload explicitly. Product browsing, checkout, notifications and scheduled jobs stress different parts of the system. Response-time percentiles, throughput, errors and background-job delay can be examined as load increases. Any reported capacity should identify the environment and workload used. Geographical expansion should be examined separately by configuring an additional service area and verifying that applicable rules and workflows remain coherent.

Finally, evaluation must distinguish implementation, integration and real-world operation. A payment sandbox demonstrates a different level of evidence from live settlement; a location-update demonstration differs from sustained rider tracking; an available mobile codebase differs from verified deployment on each target platform. These distinctions allow the results chapter to make precise claims without overstating the prototype's maturity.

## 2.17 Chapter summary

This chapter establishes that a location-based grocery platform requires coordinated information and fulfilment across customers, vendors and riders. The reviewed research supports examining perceived benefit and usability alongside trust, availability, delivery and practical access. It also shows why scheduling, recurring purchasing and independent-vendor operations create requirements that extend beyond a conventional shopping interface.

The architectural discussion identifies shared services, transaction coordination, background processing and configurable service areas as relevant design approaches. Their presence supports an argument about design intent, while functional and performance evaluation must establish what the implementation achieves. The Sri Lankan evidence and local platform comparison provide context without assuming that Batticaloa lacks digital alternatives or that findings from other populations transfer unchanged.

DailyCart's contribution is therefore positioned as the context-specific design and evaluation of an integrated grocery and daily-essential delivery system. The six-construct acceptance framework connects consumer evidence to requirements, while a separate technical evaluation addresses correctness, usability and growth. Together, these perspectives provide the foundation for the methodology, system design and evaluation chapters.
