from pathlib import Path
import re, json
from docx import Document
from docx.shared import Inches, Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_CELL_VERTICAL_ALIGNMENT
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
from docx.opc.constants import RELATIONSHIP_TYPE as RT
from PIL import Image, ImageDraw, ImageFont

ROOT = Path(__file__).resolve().parent
OUT = ROOT / 'DailyCart_Final_Literature_Review.docx'
doc = Document()
sec = doc.sections[0]
sec.page_width, sec.page_height = Inches(8.27), Inches(11.69)
sec.top_margin = sec.bottom_margin = Inches(0.85)
sec.left_margin = Inches(1.1)
sec.right_margin = Inches(0.9)
sec.footer_distance = Inches(0.35)
normal = doc.styles['Normal']
normal.font.name = 'Times New Roman'
normal.font.size = Pt(12)
normal.paragraph_format.line_spacing = 1.5
normal.paragraph_format.space_after = Pt(6)
normal.paragraph_format.widow_control = True
for name, size in [('Title',18),('Heading 1',14),('Heading 2',12),('Caption',10)]:
    st=doc.styles[name]
    st.font.name='Times New Roman'
    st.font.size=Pt(size)
    st.font.color.rgb=RGBColor(0,0,0)
    st.font.bold=name!='Caption'
    st.paragraph_format.space_before=Pt(12 if name!='Title' else 0)
    st.paragraph_format.space_after=Pt(6)
    st.paragraph_format.line_spacing=1.15
    if name!='Caption': st.paragraph_format.keep_with_next=True

# Remove template borders and theme font overrides from all styles.
for st in doc.styles:
    for border in list(st.element.iter(qn('w:pBdr'))):
        border.getparent().remove(border)
    for fonts in st.element.iter(qn('w:rFonts')):
        for key in list(fonts.attrib):
            if key.endswith('Theme'): del fonts.attrib[key]
        fonts.set(qn('w:ascii'),'Times New Roman')
        fonts.set(qn('w:hAnsi'),'Times New Roman')

def hyperlink(p, text, url):
    rel=p.part.relate_to(url, RT.HYPERLINK, is_external=True)
    h=OxmlElement('w:hyperlink');h.set(qn('r:id'),rel)
    r=OxmlElement('w:r');pr=OxmlElement('w:rPr')
    col=OxmlElement('w:color');col.set(qn('w:val'),'000000');pr.append(col)
    r.append(pr); t=OxmlElement('w:t');t.text=text;r.append(t);h.append(r);p._p.append(h)

def table(caption, headers, rows, widths):
    p=doc.add_paragraph(caption,'Caption');p.paragraph_format.keep_with_next=True
    t=doc.add_table(rows=1, cols=len(headers));t.alignment=WD_TABLE_ALIGNMENT.CENTER;t.autofit=False
    for c,w in zip(t.columns,widths):c.width=Inches(w)
    for rowdata in [headers]+rows:
        cells=t.rows[0].cells if rowdata is headers else t.add_row().cells
        for cell,txt,w in zip(cells,rowdata,widths):
            cell.width=Inches(w);cell.vertical_alignment=WD_CELL_VERTICAL_ALIGNMENT.CENTER
            p=cell.paragraphs[0];p.paragraph_format.line_spacing=1.08;p.paragraph_format.space_after=Pt(3);p.paragraph_format.space_before=Pt(3)
            r=p.add_run(txt);r.font.size=Pt(10.5)
            tcPr=cell._tc.get_or_add_tcPr()
            borders=OxmlElement('w:tcBorders')
            for edge in ['top','left','bottom','right']:
                e=OxmlElement('w:'+edge);e.set(qn('w:val'),'single');e.set(qn('w:sz'),'4');e.set(qn('w:color'),'D9D9D9');borders.append(e)
            tcPr.append(borders)
            margins=OxmlElement('w:tcMar')
            for edge in ['top','left','bottom','right']:
                e=OxmlElement('w:'+edge);e.set(qn('w:w'),'85');e.set(qn('w:type'),'dxa');margins.append(e)
            tcPr.append(margins)
            if rowdata is headers:
                r.bold=True;sh=OxmlElement('w:shd');sh.set(qn('w:fill'),'E7E6E6');tcPr.append(sh)
        row=cells[0]._tc.getparent()
        trPr=row.get_or_add_trPr();trPr.append(OxmlElement('w:cantSplit'))
    repeat=OxmlElement('w:tblHeader');t.rows[0]._tr.get_or_add_trPr().append(repeat)
    doc.add_paragraph().paragraph_format.space_after=Pt(0)

def framework():
    im=Image.new('RGB',(1800,1000),'white');d=ImageDraw.Draw(im)
    font=ImageFont.truetype('C:/Windows/Fonts/times.ttf',38)
    small=ImageFont.truetype('C:/Windows/Fonts/times.ttf',31)
    labels=['Convenience','System usability','Trust and security','Delivery quality','Product availability','Regional accessibility']
    for i,label in enumerate(labels):
        y=80+i*135
        d.rectangle((40,y,690,y+95),outline='black',width=3)
        label=f'H{i+1}  '+label
        box=d.textbbox((0,0),label,font=font); d.text((365-(box[2]-box[0])/2,y+26),label,font=font,fill='black')
        end=(1200,410+i*32);start=(690,y+48)
        d.line([start,end],fill='black',width=3)
        import math
        a=math.atan2(end[1]-start[1],end[0]-start[0]);length=20
        d.polygon([end,(end[0]-length*math.cos(a-.45),end[1]-length*math.sin(a-.45)),(end[0]-length*math.cos(a+.45),end[1]-length*math.sin(a+.45))],fill='black')
    d.rectangle((1200,370,1760,630),outline='black',width=3)
    for j,label in enumerate(['Adoption intention','Willingness to use','DailyCart']):
        box=d.textbbox((0,0),label,font=font);d.text((1480-(box[2]-box[0])/2,415+j*57),label,font=font,fill='black')
    d.text((45,930),'H1 to H6 propose positive associations; technical evaluation is assessed separately.',font=small,fill='black')
    path=ROOT/'framework.png';im.save(path)
    p=doc.add_paragraph();p.paragraph_format.keep_with_next=True;p.add_run().add_picture(str(path),width=Inches(6.2))
    p=doc.add_paragraph('Figure 2.1 Proposed consumer acceptance framework for DailyCart','Caption')
    p.paragraph_format.keep_with_next=False

platform_rows=[
['KKY Bazar','Local marketplace; Kattankudy focus described by its developer','Local vendor participation','Full fulfilment and recurring-order behaviour not verified'],
['Savari','Batticaloa named in public multi-vendor delivery material','Local delivery coordination','Marketing descriptions do not establish every operational feature'],
['PickMe Market','Groceries, merchant participation, tracking and payment choices','Delivery visibility and essential-item ordering','Later scheduling stated unavailable on the reviewed service page'],
['Cargills Online','Retailer-operated grocery catalogue and customer ordering','Product information and organised grocery shopping','Independent-vendor coordination differs from DailyCart'],
['DailyCart','Web and mobile system under study with vendor-specific orders','Integrated inventory, scheduling, payments and delivery','Usability, capacity and operational outcomes require evaluation']]
evidence_rows=[
['Davis (1989)','Acceptance-scale development','Usefulness and ease of use','General technology context; not a grocery fulfilment evaluation'],
['Tyrväinen and Karjaluoto (2022)','Meta-analysis; 50 samples','Purchase intention depends on perceptions and context','Pandemic and non-pandemic settings differ'],
['Wijeratne and Kuruppu (2022)','Sri Lankan survey; 200 respondents','Usefulness and ease of use associated with intention','Does not evaluate the DailyCart artefact or district-wide demand'],
['Malekpour et al. (2026)','Review and weight analysis; 40 studies','Service attributes extend beyond the interface','Consistency of findings is not a local causal ranking'],
['Agatz et al. (2011)','Delivery time-slot research','Customer choice and operating considerations interact','Does not prescribe a 30-minute preparation rule'],
['Wagner et al. (2021)','Retail transaction-data analysis','Delivery subscriptions can increase costs as well as sales','Paid delivery membership differs from recurring product orders'],
['Hagiu and Wright (2015)','Platform economic framework','Independent participation changes coordination','Does not specify grocery software workflows'],
['Hevner et al. (2004)','Information-systems research framework','Artefact development requires evaluation','Does not itself establish the utility of DailyCart']]
construct_rows=[
['Convenience','Time, travel and repeated shopping effort','Recurring orders, scheduling and combined shopping','Expected usefulness is not measured time saving'],
['System usability','Learning, clarity and task effort','Store discovery and web/mobile ordering','Combine perceptions with task completion and errors'],
['Trust and security','Confidence in information and transactions','Verified payment states, permissions and refunds','Perceived protection differs from tested access control'],
['Delivery quality','Timeliness, accuracy and visibility','Preparation, rider updates and delivery evidence','Status updates do not prove real punctuality'],
['Product availability','Dependable products and stock information','Vendor inventory, variants and stock checks','Internal consistency differs from physical shelf accuracy'],
['Regional accessibility','Practical ability to access the service','Addresses, coverage rules and payment choices','Configured geography differs from operating coverage']]

parts=(ROOT/'chapter.md').read_text(encoding='utf-8').split('\n\n')
for block in parts:
    block=block.strip()
    if not block:continue
    if block.startswith('CHAPTER 2'):
        p=doc.add_paragraph('CHAPTER 2','Title');p.alignment=WD_ALIGN_PARAGRAPH.CENTER
        p=doc.add_paragraph('LITERATURE REVIEW','Title');p.alignment=WD_ALIGN_PARAGRAPH.CENTER
        p=doc.add_paragraph('Design and Development of a Scalable Location-Based Online Grocery and Daily Essential Delivery System for Batticaloa District, Sri Lanka: A Case Study of DailyCart')
        p.alignment=WD_ALIGN_PARAGRAPH.CENTER;p.paragraph_format.line_spacing=1.15;p.paragraph_format.space_after=Pt(16)
        for r in p.runs:r.bold=True;r.font.size=Pt(11)
    elif block.startswith('### '):doc.add_paragraph(block[4:],'Heading 2')
    elif block.startswith('## '):doc.add_paragraph(block[3:],'Heading 1')
    elif block=='[[PLATFORM_TABLE]]':table('Table 2.1 Comparison of relevant platforms as reviewed on 31 October 2026',['Platform','Publicly described model','Relevance to DailyCart','Evidence boundary'],platform_rows,[1,1.8,1.55,1.9])
    elif block=='[[EVIDENCE_TABLE]]':table('Table 2.2 Selected evidence and limitations',['Source','Evidence type','Contribution','Limit for this study'],evidence_rows,[1.35,1.3,1.55,2.05])
    elif block=='[[CONSTRUCT_TABLE]]':table('Table 2.3 Constructs and corresponding system requirements',['Construct','Meaning','Related system functions','Evaluation distinction'],construct_rows,[1.2,1.5,1.65,1.9])
    elif block=='[[FRAMEWORK_FIGURE]]':framework()
    else:
        p=doc.add_paragraph(block);p.alignment=WD_ALIGN_PARAGRAPH.JUSTIFY

refs=[
('Agatz, N., Campbell, A. M., Fleischmann, M., & Savelsbergh, M. (2011). Time slot management in attended home delivery. Transportation Science, 45(3), 435–449.','https://doi.org/10.1287/trsc.1100.0346'),
('Cargills Online. (n.d.). Cargills Online. Retrieved October 31, 2026, from','https://cargillsonline.com/'),
('Davis, F. D. (1989). Perceived usefulness, perceived ease of use, and user acceptance of information technology. MIS Quarterly, 13(3), 319–340.','https://doi.org/10.2307/249008'),
('Department of Census and Statistics. (2024). Computer literacy statistics 2024: Annual bulletin.','https://www.statistics.gov.lk/Resource/en/ComputerLiteracy/Bulletins/AnnualBuletinComputerLiteracy-2024.pdf'),
('Fielding, R. T. (2000). Architectural styles and the design of network-based software architectures [Doctoral dissertation, University of California, Irvine].','https://ics.uci.edu/~fielding/pubs/dissertation/fielding_dissertation.pdf'),
('Gefen, D., Karahanna, E., & Straub, D. W. (2003). Trust and TAM in online shopping: An integrated model. MIS Quarterly, 27(1), 51–90.','https://doi.org/10.2307/30036519'),
('Hagiu, A., & Wright, J. (2015). Multi-sided platforms (Working Paper No. 15-037). Harvard Business School.','https://dash.harvard.edu/bitstreams/7312037d-7dd3-6bd4-e053-0100007fdf3b/download'),
('Hevner, A. R., March, S. T., Park, J., & Ram, S. (2004). Design science in information systems research. MIS Quarterly, 28(1), 75–105.','https://aisel.aisnet.org/misq/vol28/iss1/6/'),
('International Organization for Standardization. (2018). Ergonomics of human-system interaction — Part 11: Usability: Definitions and concepts (ISO Standard No. 9241-11:2018).','https://committee.iso.org/standard/63500.html'),
('KKY Bazar. (n.d.). KKY Bazar: Sri Lanka local marketplace. Retrieved October 31, 2026, from','https://www.kkybazar.lk/'),
('Malekpour, M., Kizgin, H., & Okumus, F. (2026). Online grocery service attributes and customer satisfaction: A weight analysis approach. Electronic Commerce Research. Advance online publication.','https://doi.org/10.1007/s10660-026-10186-5'),
('Microsoft. (n.d.-a). Competing consumers pattern. Azure Architecture Center. Retrieved October 31, 2026, from','https://learn.microsoft.com/en-us/azure/architecture/patterns/competing-consumers'),
('Microsoft. (n.d.-b). Queue-based load leveling pattern. Azure Architecture Center. Retrieved October 31, 2026, from','https://learn.microsoft.com/en-us/azure/architecture/patterns/queue-based-load-leveling'),
('Oracle. (n.d.). Locking reads. MySQL 8.0 reference manual. Retrieved October 31, 2026, from','https://dev.mysql.com/doc/refman/8.0/en/innodb-locking-reads.html'),
('OWASP. (2023). API1:2023 broken object level authorization. OWASP API Security Top 10.','https://api-security.owasp.org/editions/2023/en/0xa1-broken-object-level-authorization/'),
('Parasuraman, A., Zeithaml, V. A., & Malhotra, A. (2005). E-S-QUAL: A multiple-item scale for assessing electronic service quality. Journal of Service Research, 7(3), 213–233.','https://doi.org/10.1177/1094670504271156'),
('PayHere. (n.d.). Checkout API. PayHere Knowledge Base. Retrieved October 31, 2026, from','https://support.payhere.lk/api-%26-mobile-sdk/checkout-api'),
('PickMe. (n.d.). Market. Retrieved October 31, 2026, from','https://pickme.lk/services/market/'),
('Rogers, E. M. (2003). Diffusion of innovations (5th ed.). Free Press.','https://www.simonandschuster.com/books/Diffusion-of-Innovations-5th-Edition/Everett-M-Rogers/9780743258234'),
('Savari Delivery Service. (n.d.). Savari Delivery Service. Retrieved October 31, 2026, from','https://savarinow.com/'),
('Tyrväinen, O., & Karjaluoto, H. (2022). Online grocery shopping before and during the COVID-19 pandemic: A meta-analytical review. Telematics and Informatics, 71, Article 101839.','https://doi.org/10.1016/j.tele.2022.101839'),
('Velam.ai. (n.d.). KKY Bazar: Digitizing Kattankudy’s commerce. Retrieved October 31, 2026, from','https://www.velam.ai/case-studies/kky-bazar'),
('Venkatesh, V., Morris, M. G., Davis, G. B., & Davis, F. D. (2003). User acceptance of information technology: Toward a unified view. MIS Quarterly, 27(3), 425–478.','https://doi.org/10.2307/30036540'),
('Venkatesh, V., Thong, J. Y. L., & Xu, X. (2012). Consumer acceptance and use of information technology: Extending the unified theory of acceptance and use of technology. MIS Quarterly, 36(1), 157–178.','https://doi.org/10.2307/41410412'),
('Wagner, L., Pinto, C., & Amorim, P. (2021). On the value of subscription models for online grocery retail. European Journal of Operational Research, 294(3), 874–894.','https://doi.org/10.1016/j.ejor.2020.05.011'),
('Wijeratne, D. U. S., & Kuruppu, I. V. (2022). Consumer intention toward online grocery shopping during the post-pandemic (Covid-19). Asian Journal of Marketing Management, 2(1), 27–40.','https://journals.sjp.ac.lk/index.php/ajmm/article/download/6250/4563/17187'),
('Xu, H., Teo, H.-H., & Tan, B. C. Y. (2005). Predicting the adoption of location-based services: The role of trust and perceived privacy risk. ICIS 2005 Proceedings, Paper 71.','https://aisel.aisnet.org/icis2005/71/')]
doc.add_page_break();doc.add_paragraph('References','Heading 1')
for text,url in refs:
    p=doc.add_paragraph(text+' ')
    p.paragraph_format.line_spacing=1.15;p.paragraph_format.space_after=Pt(9)
    p.paragraph_format.left_indent=Inches(.3);p.paragraph_format.first_line_indent=Inches(-.3)
    p.paragraph_format.keep_together=True
    hyperlink(p,url,url)
footer=sec.footer.paragraphs[0];footer.alignment=WD_ALIGN_PARAGRAPH.CENTER
r=footer.add_run();fld=OxmlElement('w:fldSimple');fld.set(qn('w:instr'),'PAGE');r._r.addnext(fld)
doc.core_properties.title='DailyCart Literature Review'
doc.core_properties.subject='Chapter 2 aligned with the research title and final system scope'
doc.core_properties.author='Umer Ahsen'
doc.core_properties.keywords='DailyCart, Batticaloa, online grocery, location-based delivery, scalability'
doc.save(OUT)
print(json.dumps({'output':str(OUT),'body_words':len(re.findall(r"\b[\w’-]+\b",(ROOT/'chapter.md').read_text(encoding='utf-8'))),'references':len(refs),'tables':len(doc.tables)}))
