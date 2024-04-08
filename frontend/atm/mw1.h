#ifndef MW1_H
#define MW1_H

#include <QMainWindow>
#include "rfid.h"

QT_BEGIN_NAMESPACE
namespace Ui {
class mw1;
}
QT_END_NAMESPACE

class mw1 : public QMainWindow
{
    Q_OBJECT

public:
    mw1(QWidget *parent = nullptr);
    ~mw1();

private slots:
    void on_pb_card_clicked();

    void on_pb_login_clicked();

    void on_pb_ins_card_clicked();

    void fetch_card_data();

private:
    Ui::mw1 *ui;
    bool card_ins;
    RFID *cardReader;
    QString cardNumber;
};
#endif // MW1_H
